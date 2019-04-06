<?php

namespace App\Repository\CreditCard;

use App\Entity\CreditCard\CreditCardPayments;
use App\Entity\CreditCard\CreditCardUser;
use App\Entity\Security\User;
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

    public function getCreditCardUsersByOwner($owner)
    {
        return $this->createQueryBuilder('ccu')
            ->where('ccu.parent = :parent')
            ->andWhere('ccu.deletedAt IS NULL')
            ->setParameter('parent', $owner)
            ;
    }

    // /**
    //  * @return Payments[] Returns an array of Payments objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Payments
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
