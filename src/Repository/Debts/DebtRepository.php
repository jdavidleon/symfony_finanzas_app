<?php

namespace App\Repository\Debts;

use App\Entity\Debts\DebtsBalance;
use App\Entity\Debts\DebtsTypes;
use App\Entity\Security\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method DebtsTypes|null find($id, $lockMode = null, $lockVersion = null)
 * @method DebtsTypes|null findOneBy(array $criteria, array $orderBy = null)
 * @method DebtsTypes[]    findAll()
 * @method DebtsTypes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DebtRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, DebtsTypes::class);
    }

    public function getActualDebtsByUser(User $user)
    {
        return $this->createQueryBuilder('d')
            ->leftJoin(DebtsBalance::class, 'db')
            ->where('d.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

}
