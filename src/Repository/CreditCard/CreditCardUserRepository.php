<?php

namespace App\Repository\CreditCard;

use App\Entity\CreditCard\CreditCardUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CreditCardUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method CreditCardUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method CreditCardUser[]    findAll()
 * @method CreditCardUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CreditCardUserRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CreditCardUser::class);
    }

    // /**
    //  * @return CreditCardUser[] Returns an array of CreditCardUser objects
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
    public function findOneBySomeField($value): ?CreditCardUser
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
