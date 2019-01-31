<?php

namespace App\Repository\Debts;


use App\Entity\Debts\FixedCharges;
use App\Entity\Security\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class FixedChargesRepository extends ServiceEntityRepository
{

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, FixedCharges::class);
    }
    /**
     * @param User $user
     * @return mixed
     */
    public function getActualMonthFixedChargesByUser(User $user)
    {
        return $this->createQueryBuilder('fc')
            ->where('fc.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    public function getFixedChargesListByUser(User $user)
    {
        return $this->createQueryBuilder('fc')
            ->where('fc.user = :user')
            ->andWhere('fc.status <> :status_invalid')
            ->andWhere('fc.deletedAt IS NOT NULL')
            ->setParameters([
                'user' => $user,
                'status_invalid' => FixedCharges::INVALID
            ])
            ->getQuery()
            ->getResult();
    }
}
