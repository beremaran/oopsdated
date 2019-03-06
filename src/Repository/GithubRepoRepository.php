<?php

namespace App\Repository;

use App\Entity\GithubRepo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method GithubRepo|null find($id, $lockMode = null, $lockVersion = null)
 * @method GithubRepo|null findOneBy(array $criteria, array $orderBy = null)
 * @method GithubRepo[]    findAll()
 * @method GithubRepo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GithubRepoRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, GithubRepo::class);
    }

    /**
     * @param $n
     * @return GithubRepo[] Returns an array of GithubRepo objects
     */
    public function getPopularRepositories($n)
    {
        return $this->createQueryBuilder('gh')
            ->select('gh, COUNT(se) AS HIDDEN nSubscriptions')
            ->leftJoin('gh.subscribedEmails', 'se')
            ->groupBy('gh')
            ->orderBy('nSubscriptions', 'DESC')
            ->setMaxResults($n)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param string $name
     * @return GithubRepo|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneByName(string $name)
    {
        return $this->createQueryBuilder('gh')
            ->where('gh.name = :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult();
    }

    // /**
    //  * @return Repository[] Returns an array of Repository objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Repository
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
