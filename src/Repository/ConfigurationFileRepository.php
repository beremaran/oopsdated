<?php

namespace App\Repository;

use App\Entity\ConfigurationFile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ConfigurationFile|null find($id, $lockMode = null, $lockVersion = null)
 * @method ConfigurationFile|null findOneBy(array $criteria, array $orderBy = null)
 * @method ConfigurationFile[]    findAll()
 * @method ConfigurationFile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConfigurationFileRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ConfigurationFile::class);
    }

    /**
     * @param $repoName
     * @param $packageManagerType
     * @return ConfigurationFile
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function get($repoName, $packageManagerType)
    {
        return $this->createQueryBuilder('cf')
            ->leftJoin('cf.repository', 'r')
            ->where('cf.packageManagerType = :pmType')
            ->andWhere('r.name = :repoName')
            ->setParameters([
                'repoName' => $repoName,
                'pmType' => $packageManagerType
            ])
            ->getQuery()
            ->getOneOrNullResult();
    }

    // /**
    //  * @return ConfigurationFile[] Returns an array of ConfigurationFile objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ConfigurationFile
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
