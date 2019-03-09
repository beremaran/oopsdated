<?php
/**
 * Created by PhpStorm.
 * User: beremaran
 * Date: 07.03.2019
 * Time: 15:20
 */

namespace App\Processors;


use App\Entity\GithubRepo;
use App\Entity\Package;
use App\Entity\ScanResult;
use App\Repository\ConfigurationFileRepository;
use App\Repository\GithubRepoRepository;
use App\Service\ConfigurationParserFactory;
use App\Service\GithubClient;
use App\Service\RegistryFactory;
use Doctrine\ORM\EntityManagerInterface;
use Enqueue\Client\ProducerInterface;
use Enqueue\Client\TopicSubscriberInterface;
use Interop\Queue\Context;
use Interop\Queue\Message;
use Interop\Queue\Processor;
use Jelix\Version\VersionComparator;
use Psr\Log\LoggerInterface;

class ScanRepoProcessor implements Processor, TopicSubscriberInterface
{
    protected $logger;
    protected $producer;
    protected $githubClient;
    protected $repoRepository;
    protected $registryFactory;
    protected $parserFactory;
    protected $configRepository;
    protected $entityManager;

    public function __construct(LoggerInterface $logger,
                                ProducerInterface $producer,
                                GithubClient $githubClient,
                                GithubRepoRepository $repoRepository,
                                RegistryFactory $registryFactory,
                                ConfigurationParserFactory $parserFactory,
                                ConfigurationFileRepository $configRepository,
                                EntityManagerInterface $entityManager)
    {
        $this->logger = $logger;
        $this->producer = $producer;
        $this->githubClient = $githubClient;
        $this->repoRepository = $repoRepository;
        $this->registryFactory = $registryFactory;
        $this->parserFactory = $parserFactory;
        $this->configRepository = $configRepository;
        $this->entityManager = $entityManager;
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
     * @throws \Exception
     */
    public function process(Message $message, Context $context)
    {
        $data = json_decode($message->getBody(), true);
        $repoName = $data['repoName'];
        if ($repoName === null) {
            return self::REJECT;
        }

        $onlyReportTo = $data['onlyReportTo'] ?? null;
        $repo = $this->getOrFetchRepo($repoName);

        $nOutdated = 0;
        $repoDependencies = [];
        $packageManagers = $this->parserFactory->getPackageManagerTypes();
        foreach ($packageManagers as $packageManager) {
            $parser = $this->parserFactory->get($packageManager);
            $registry = $this->registryFactory->get($packageManager);
            if ($registry === null) {
                $this->logger->debug('Could not find ' . $packageManager . ' registry.');
                continue;
            }

            $configFile = $this->configRepository->get($repo->getName(), $packageManager);
            if ($configFile === null) {
                $this->logger->debug($repo->getName() . ' does not have a ' . $packageManager . ' configuration.');
                continue;
            }

            $parser->parse($configFile->getContent());
            $dependencies = $parser->getDependencies();

            foreach ($dependencies as $i => $package) {
                $latestPackage = $registry->getPackageByName($package->getName());
                if ($latestPackage === null) {
                    $dependencies[$i] = null;
                    continue;
                }

                $isOutdated = $this->isOutdated($package, $latestPackage);
                if ($isOutdated) {
                    $nOutdated++;
                }

                $dependencies[$i] = [
                    'package' => $package->getName(),
                    'outdated' => $isOutdated,
                    'version' => $package->getVersion(),
                    'latestVersion' => $latestPackage->getVersion()
                ];
            }

            $repoDependencies[] = [
                'name' => $packageManager,
                'dependencies' => array_filter($dependencies, function ($d) {
                    return $d !== null;
                })
            ];
        }

        $scanResult = $repo->getScanResult();
        if ($scanResult === null) {
            $scanResult = new ScanResult();
            $scanResult->setRepository($repo);
        }

        $scanResult->setUpdatedAt(new \DateTime());
        $scanResult->setJsonReport([
            'nOutdated' => $nOutdated,
            'report' => $repoDependencies
        ]);

        $this->entityManager->persist($scanResult);
        $this->entityManager->flush();

        if ($nOutdated > 0) {
            $this->logger->info('Sending reports ...');
            $this->producer->sendEvent('mailReport', json_encode([
                'repoName' => $repoName,
                'nOutdated' => $nOutdated,
                'report' => $repoDependencies,
                'onlyReportTo' => $onlyReportTo
            ]));
        } elseif ($onlyReportTo !== null) {
            $this->producer->sendEvent('mailReport', json_encode([
                'nOutdated' => 0,
                'repoName' => $repoName,
                'onlyReportTo' => $onlyReportTo
            ]));
        }

        return self::ACK;
    }

    /**
     * @param Package $package
     * @return bool
     */
    public function isDevelopmentVersion($package): bool
    {
        return strpos($package->getVersion(), 'dev') !== false;
    }

    /**
     * @param Package $package
     * @param Package $latestPackage
     * @return bool
     */
    public function isOutdated($package, $latestPackage): bool
    {
        if (!$this->isDevelopmentVersion($package)) {
            $version = $package->getVersion();
            $latestVersion = $latestPackage->getVersion();

            return !VersionComparator::compareVersionRange($latestVersion, $version);
        }

        return false;
    }

    /**
     * @param $repoName
     * @return GithubRepo|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Exception
     */
    public function getOrFetchRepo($repoName): ?GithubRepo
    {
        $repo = $this->repoRepository->findOneByName($repoName);
        if ($repo === null) {
            return null;
        }

        $tDiff = (new \DateTime())->getTimestamp() - $repo->getUpdatedAt()->getTimestamp();
        if ($tDiff > 3600) {
            return $this->githubClient->fetchRepository($repoName);
        }

        return $repo;
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
        return ['scanRepo'];
    }
}