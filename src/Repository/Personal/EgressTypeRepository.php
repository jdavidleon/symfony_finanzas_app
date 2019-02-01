<?php

namespace App\Repository\Personal;

use App\Entity\Personal\EgressType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method EgressType|null find($id, $lockMode = null, $lockVersion = null)
 * @method EgressType|null findOneBy(array $criteria, array $orderBy = null)
 * @method EgressType[]    findAll()
 * @method EgressType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EgressTypeRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, EgressType::class);
    }

    // /**
    //  * @return EgressType[] Returns an array of EgressType objects
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
    public function findOneBySomeField($value): ?EgressType
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
