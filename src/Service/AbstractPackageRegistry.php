<?php
/**
 * Created by PhpStorm.
 * User: beremaran
 * Date: 06.03.2019
 * Time: 20:09
 */

namespace App\Service;


use App\Entity\Package;
use App\Repository\PackageRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;

abstract class AbstractPackageRegistry
{
    protected $entityManager;
    protected $packageRepository;
    protected $guzzleClient;
    protected $logger;

    public function __construct(Client $guzzleClient,
                                LoggerInterface $logger,
                                PackageRepository $packageRepository,
                                EntityManagerInterface $entityManager)
    {
        $this->packageRepository = $packageRepository;
        $this->entityManager = $entityManager;
        $this->guzzleClient = $guzzleClient;
        $this->logger = $logger;
    }

    /**
     * @param string $name
     * @return Package|null
     */
    public function getPackageByName(string $name)
    {

        try {
            $package = $this->packageRepository->findOneByName($name);
        } catch (NonUniqueResultException $e) {
            $package = null;
        }

        if ($package !== null) {
            try {
                $diff = (new DateTime())->getTimestamp() - $package->getUpdatedAt()->getTimestamp();
            } catch (\Exception $e) {
                $diff = 0;
            }

            if ($diff < 60 * 60) {
                return $package;
            }
        }

        $packageURI = $this->getURIForPackage($name);
        $response = null;

        try {
            $response = $this->guzzleClient->get($packageURI);
        } catch (\Exception $err) {
            $this->logger->error($err->getMessage());
            return null;
        }

        if ($response->getStatusCode() != 200) {
            return null;
        }

        $meta = json_decode($response->getBody(), true);
        if ($meta == null) {
            return null;
        }

        $parsedPackage = $this->parsePackageMeta($meta);
        if ($parsedPackage === null)
            return $package;

        if ($package === null) {
            $package = $parsedPackage;
        } else {
            $package->setVersion($parsedPackage->getVersion());
        }

        try {
            $package->setUpdatedAt(new \DateTime());
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

        $this->entityManager->persist($package);
        $this->entityManager->flush();
        return $package;
    }

    /**
     * @param string $name
     * @return string
     */
    protected abstract function getURIForPackage(string $name): string;

    /**
     * @param array $meta
     * @return Package
     */
    protected abstract function parsePackageMeta(array $meta): Package;

    /**
     * @return string
     */
    public abstract function getPackageManagerType(): string;
}