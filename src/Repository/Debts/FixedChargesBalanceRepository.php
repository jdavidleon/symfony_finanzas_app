<?php

namespace App\Repository\Debts;

use App\Entity\Debts\FixedChargePayment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method FixedChargePayment|null find($id, $lockMode = null, $lockVersion = null)
 * @method FixedChargePayment|null findOneBy(array $criteria, array $orderBy = null)
 * @method FixedChargePayment[]    findAll()
 * @method FixedChargePayment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FixedChargesBalanceRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, FixedChargePayment::class);
    }

    // /**
    //  * @return FixedChargesBalance[] Returns an array of FixedChargesBalance objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?FixedChargesBalance
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
