<?php

namespace App\Repository\CreditCard;

use App\Entity\CreditCard\CreditCardConsume;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CreditCardConsume|null find($id, $lockMode = null, $lockVersion = null)
 * @method CreditCardConsume|null findOneBy(array $criteria, array $orderBy = null)
 * @method CreditCardConsume[]    findAll()
 * @method CreditCardConsume[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CreditCardConsumeRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CreditCardConsume::class);
    }

//    public function getDebtPaymentsByConsume($consume)
//    {
//        return $this->createQueryBuilder('c')
//            ->join('c.payments', 'cp')
//            ->where('c.id = :consume')
//            ->set
//    }

    // /**
    //  * @return CreditCardConsume[] Returns an array of CreditCardConsume objects
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
    public function findOneBySomeField($value): ?CreditCardConsume
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
