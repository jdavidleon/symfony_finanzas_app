<?php

namespace App\Repository\Debts;

use App\Entity\Debts\Debt;
use App\Entity\Security\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Debt|null find($id, $lockMode = null, $lockVersion = null)
 * @method Debt|null findOneBy(array $criteria, array $orderBy = null)
 * @method Debt[]    findAll()
 * @method Debt[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DebtRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Debt::class);
    }

    // /**
    //  * @return Debt[] Returns an array of Debt objects
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
    public function findOneBySomeField($value): ?Debt
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function getActualDebtsByUser(User $user)
    {
        return $this->createQueryBuilder('d')
            ->where('d.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    public function getNextDebtsByUserAndComingDays(User $user,?int $comingDays = null)
    {
        return $this->createQueryBuilder('d')
            ->where('d.user = :user')
            ->andWhere('d.balance > :balance')
            ->andWhere('d.paymentDay')
            ->setParameters([
                'user' => $user,
                'balance' => 0
            ])
            ->getQuery()
            ->getResult();
    }
}
