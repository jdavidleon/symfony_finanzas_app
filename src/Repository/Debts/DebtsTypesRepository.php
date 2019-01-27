<?php

namespace App\Repository\Debts;

use App\Entity\Debts\DebtsTypes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method DebtsTypes|null find($id, $lockMode = null, $lockVersion = null)
 * @method DebtsTypes|null findOneBy(array $criteria, array $orderBy = null)
 * @method DebtsTypes[]    findAll()
 * @method DebtsTypes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DebtsTypesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, DebtsTypes::class);
    }

    // /**
    //  * @return DebtsTypes[] Returns an array of DebtsTypes objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DebtsTypes
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
