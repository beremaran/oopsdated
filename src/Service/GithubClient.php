<?php
/**
 * Created by PhpStorm.
 * User: beremaran
 * Date: 07.03.2019
 * Time: 14:51
 */

namespace App\Service;


use App\Entity\ConfigurationFile;
use App\Entity\GithubRepo;
use App\Repository\ConfigurationFileRepository;
use App\Repository\GithubRepoRepository;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Symfony\Component\Cache\Simple\FilesystemCache;

class GithubClient
{
    /**
     * @var FilesystemCache
     */
    protected $cache;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Client
     */
    protected $guzzleClient;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var ConfigurationFileRepository
     */
    protected $configurationFileRepository;

    /**
     * @var ConfigurationParserFactory
     */
    protected $configParserFactory;

    /**
     * @var GithubRepoRepository
     */
    protected $repoRepository;

    public function __construct(LoggerInterface $logger,
                                Client $guzzleClient,
                                EntityManagerInterface $entityManager,
                                ConfigurationParserFactory $configParserFactory,
                                ConfigurationFileRepository $configurationFileRepository,
                                GithubRepoRepository $repoRepository)
    {
        $this->cache = new FilesystemCache();
        $this->guzzleClient = $guzzleClient;
        $this->logger = $logger;
        $this->configParserFactory = $configParserFactory;
        $this->configurationFileRepository = $configurationFileRepository;
        $this->entityManager = $entityManager;
        $this->repoRepository = $repoRepository;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function doesRepoExists(string $name): bool
    {
        $r = $this->guzzleClient->get('/repos/' . $name);
        return $r->getStatusCode() !== 404;
    }

    /**
     * @param string $name
     * @return GithubRepo|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function fetchRepository(string $name)
    {
        if (!$this->doesRepoExists($name))
            return null;

        $repo = $this->repoRepository->findOneByName($name);
        if ($repo === null) {
            $repo = new GithubRepo();
            $repo->setName($name);
        }

        $packageManagers = $this->configParserFactory->getPackageManagerTypes();
        foreach ($packageManagers as $packageManager) {
            $parser = $this->configParserFactory->get($packageManager);
            $fileContents = $this->getFileContents($name, $parser->getConfigurationFilename());
            if ($fileContents === null) {
                continue;
            }

            $configurationFile = $this->configurationFileRepository->get($name, $parser->getPackageManagerType());
            if ($configurationFile === null) {
                $configurationFile = new ConfigurationFile();
                $configurationFile->setPackageManagerType($parser->getPackageManagerType());
                $configurationFile->setRepository($repo);
            }

            $configurationFile->setRepository($repo);
            $configurationFile->setContent($fileContents);
            $repo->addConfigurationFile($configurationFile);
            $this->entityManager->persist($configurationFile);
        }

        try {
            $repo->setUpdatedAt(new \DateTime());
        } catch (\Exception $e) {
        }

        $this->entityManager->persist($repo);
        $this->entityManager->flush();
        return $repo;
    }

    protected function getFileContents(string $repoName, string $filePath)
    {
        $targetUrl = '/repos/' . $repoName . '/contents/' . $filePath;
        $cacheKey = 'getFileContents.' . md5($targetUrl);

        if ($this->cache->has($cacheKey)) {
            try {
                return $this->cache->get($cacheKey, null);
            } catch (InvalidArgumentException $e) {
                $this->logger->error($e->getMessage());
            }
        }

        $r = $this->guzzleClient->get($targetUrl);
        if ($r->getStatusCode() === 404) {
            return null;
        }

        $data = json_decode($r->getBody(), true);
        $fileContents = $data['content'];

        switch ($data['encoding']) {
            case 'base64':
                $fileContents = base64_decode($fileContents);
                break;
        }

        try {
            $this->cache->set($cacheKey, $fileContents, 60 * 5);
        } catch (InvalidArgumentException $e) {
            $this->logger->error($e->getMessage());
        }
        return $fileContents;
    }
}