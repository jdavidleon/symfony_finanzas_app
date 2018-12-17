<?php

namespace App\Repository\CreditCard;

use App\Entity\Creditcard\CreditRelation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CreditRelation|null find($id, $lockMode = null, $lockVersion = null)
 * @method CreditRelation|null findOneBy(array $criteria, array $orderBy = null)
 * @method CreditRelation[]    findAll()
 * @method CreditRelation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class creditRelationRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CreditRelation::class);
    }

    // /**
    //  * @return creditRelation[] Returns an array of creditRelation objects
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
    public function findOneBySomeField($value): ?creditRelation
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
