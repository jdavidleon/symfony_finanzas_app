<?php

namespace App\Repository\CreditCard;

use App\Entity\Creditcard\creditRelation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method creditRelation|null find($id, $lockMode = null, $lockVersion = null)
 * @method creditRelation|null findOneBy(array $criteria, array $orderBy = null)
 * @method creditRelation[]    findAll()
 * @method creditRelation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class creditRelationRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, creditRelation::class);
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
