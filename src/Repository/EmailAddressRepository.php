<?php

namespace App\Repository;

use App\Entity\EmailAddress;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method EmailAddress|null find($id, $lockMode = null, $lockVersion = null)
 * @method EmailAddress|null findOneBy(array $criteria, array $orderBy = null)
 * @method EmailAddress[]    findAll()
 * @method EmailAddress[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmailAddressRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, EmailAddress::class);
    }

    /**
     * @param $emailAddress
     * @return EmailAddress|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOne($emailAddress)
    {
        return $this->createQueryBuilder('e')
            ->where('e.email = :email')
            ->setParameter('email', $emailAddress)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param string $emailAddress
     * @return EmailAddress
     */
    public function findOneOrCreate($emailAddress): EmailAddress
    {
        $email = $this->findOne($emailAddress);
        if ($email === null) {
            $email = new EmailAddress();
            $email->setEmail($emailAddress);
            $this->getEntityManager()->persist($email);
            $this->getEntityManager()->flush($email);
        }

        return $email;
    }

    // /**
    //  * @return EmailAddress[] Returns an array of EmailAddress objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?EmailAddress
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
