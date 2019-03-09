<?php
/**
 * Created by PhpStorm.
 * User: beremaran
 * Date: 07.03.2019
 * Time: 23:44
 */

namespace App\Processors;


use App\Repository\GithubRepoRepository;
use Enqueue\Client\TopicSubscriberInterface;
use Interop\Queue\Context;
use Interop\Queue\Message;
use Interop\Queue\Processor;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class SendReportMailProcessor implements Processor, TopicSubscriberInterface
{

    protected $repoRepository;
    protected $twig;
    protected $mailer;
    protected $logger;
    protected $parameterBag;

    public function __construct(GithubRepoRepository $repoRepository,
                                \Twig_Environment $twig,
                                \Swift_Mailer $mailer,
                                LoggerInterface $logger,
                                ParameterBagInterface $parameterBag)
    {
        $this->repoRepository = $repoRepository;
        $this->twig = $twig;
        $this->mailer = $mailer;
        $this->logger = $logger;
        $this->parameterBag = $parameterBag;
    }

    /**
     * The method has to return either self::ACK, self::REJECT, self::REQUEUE string.
     *
     * The method also can return an object.
     * It must implement __toString method and the method must return one of the constants from above.
     *
     * @param Message $message
     * @param Context $context
     *
     * @return string|object with __toString method implemented
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function process(Message $message, Context $context)
    {
        $data = json_decode($message->getBody(), true);
        $repo = $this->repoRepository->findOneByName($data['repoName']);

        $error = null;

        $isFirstReport = $data['onlyReportTo'] !== null;
        $title = $isFirstReport ? 'First Time Report For ' : 'Your Daily Dependency Report for ';
        $title .= $data['repoName'];

        $templateName = $data['nOutdated'] === 0 ? 'emails/first_no_deprecation' : 'emails/scheduled_report';
        $htmlTemplate = $templateName . '.html.twig';
        $txtTemplate = $templateName . '.txt.twig';

        try {
            $message = (new \Swift_Message($title))
                ->setFrom('oopsdated@beremaran.com')
                ->setBody(
                    $this->twig->render($txtTemplate, [
                        'repoName' => $data['repoName'],
                        'nOutdated' => $data['nOutdated'],
                        'packageManagers' => $data['report']
                    ])
                )->addPart(
                    $this->twig->render($htmlTemplate, [
                        'repoName' => $data['repoName'],
                        'nOutdated' => $data['nOutdated'],
                        'packageManagers' => $data['report']
                    ]), 'text/html'
                );
        } catch (\Twig_Error_Loader $e) {
            $error = $e;
        } catch (\Twig_Error_Runtime $e) {
            $error = $e;
        } catch (\Twig_Error_Syntax $e) {
            $error = $e;
        }

        if ($error !== null) {
            $this->logger->error($error->getMessage() . "\n" . $error->getTraceAsString());
            return self::REJECT;
        }

        if ($isFirstReport) {
            $message->setTo($data['onlyReportTo']);
            $this->mailer->send($message);
        } else {
            foreach ($repo->getSubscribedEmails() as $i => $email) {
                $message->setTo($email->getEmail());
                $this->mailer->send($message);
                $this->logger->info('Sent ' . ($i + 1) . ' / ' . count($repo->getSubscribedEmails()));
            }
        }

        try {
            $this->flushMailSpool();
        } catch (ProcessFailedException $e) {
            $this->logger->error($e->getMessage() . ' - ' . $e->getFile() . ' - ' . $e->getLine());
            return self::REJECT;
        }

        return self::ACK;
    }

    /**
     * @throws ProcessFailedException
     */
    protected function flushMailSpool(): void
    {
        $process = new Process(
            ['php', './console', 'swiftmailer:spool:send']
            , $this->parameterBag->get('binDir')
        );

        $process->mustRun();
    }

    /**
     * The result maybe either:.
     *
     * 'aTopicName'
     *
     * or
     *
     * ['aTopicName', 'anotherTopicName']
     *
     * or
     *
     * [
     *   [
     *     'topic' => 'aTopicName',
     *     'processor' => 'fooProcessor',
     *     'queue' => 'a_client_queue_name',
     *
     *     'aCustomOption' => 'aVal',
     *   ],
     *   [
     *     'topic' => 'anotherTopicName',
     *     'processor' => 'barProcessor',
     *     'queue' => 'a_client_queue_name',
     *
     *     'aCustomOption' => 'aVal',
     *   ],
     * ]
     *
     * Note: If you set prefix_queue to true then the queue is used as is and therefor the driver is not used to prepare a transport queue name.
     * It is possible to pass other options, they could be accessible on a route instance through options.
     *
     * @return string|array
     */
    public static function getSubscribedTopics()
    {
        return ['mailReport'];
    }
}