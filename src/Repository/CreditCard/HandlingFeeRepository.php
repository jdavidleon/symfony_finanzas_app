<?php

namespace App\Repository\CreditCard;

use App\Entity\CreditCard\HandlingFee;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method HandlingFee|null find($id, $lockMode = null, $lockVersion = null)
 * @method HandlingFee|null findOneBy(array $criteria, array $orderBy = null)
 * @method HandlingFee[]    findAll()
 * @method HandlingFee[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HandlingFeeRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, HandlingFee::class);
    }

    // /**
    //  * @return HandlingFee[] Returns an array of HandlingFee objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('h.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?HandlingFee
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
