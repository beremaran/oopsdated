<?php
/**
 * Created by PhpStorm.
 * User: beremaran
 * Date: 07.03.2019
 * Time: 15:57
 */

namespace App\Processors;


use App\Entity\EmailAddress;
use App\Repository\EmailAddressRepository;
use App\Repository\GithubRepoRepository;
use App\Service\GithubClient;
use Doctrine\ORM\EntityManagerInterface;
use Enqueue\Client\ProducerInterface;
use Enqueue\Client\TopicSubscriberInterface;
use Interop\Queue\Context;
use Interop\Queue\Message;
use Interop\Queue\Processor;
use Psr\Log\LoggerInterface;

class CreateSubscriptionProcessor implements Processor, TopicSubscriberInterface
{

    protected $logger;
    protected $repoRepository;
    protected $githubClient;
    protected $entityManager;
    protected $emailAddressRepository;
    protected $producer;

    public function __construct(LoggerInterface $logger,
                                GithubClient $githubClient,
                                GithubRepoRepository $repoRepository,
                                EmailAddressRepository $emailAddressRepository,
                                EntityManagerInterface $entityManager,
                                ProducerInterface $producer)
    {
        $this->logger = $logger;
        $this->repoRepository = $repoRepository;
        $this->githubClient = $githubClient;
        $this->emailAddressRepository = $emailAddressRepository;
        $this->entityManager = $entityManager;
        $this->producer = $producer;
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
        $this->logger->info('Creating subscription ...');
        $data = json_decode($message->getBody(), true);

        $localRepo = $this->repoRepository->findOneByName($data['repoName']);
        if ($localRepo === null) {
            $localRepo = $this->githubClient->fetchRepository($data['repoName']);
            if ($localRepo === null) {
                return self::REJECT;
            }

        }

        $localEmailAddress = $this->emailAddressRepository->findOne($data['emailAddress']);
        if ($localEmailAddress === null) {
            $localEmailAddress = new EmailAddress();
            $localEmailAddress->setEmail($data['emailAddress']);
            $this->entityManager->persist($localEmailAddress);
        }

        $isNewSubscription = !$localRepo->getSubscribedEmails()->contains($localEmailAddress);

        if ($isNewSubscription || true) {
            $localRepo->addSubscribedEmail($localEmailAddress);
            $localEmailAddress->addGithubRepo($localRepo);

            $this->entityManager->persist($localRepo);
            $this->entityManager->persist($localEmailAddress);

            $this->entityManager->flush();

            $this->producer->sendEvent('scanRepo', json_encode([
                'onlyReportTo' => $isNewSubscription ? $localEmailAddress->getEmail() : null,
                'repoName' => $localRepo->getName()
            ]));
        }

        return self::ACK;
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
        return ['createSubscription'];
    }
}