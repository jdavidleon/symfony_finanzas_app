<?php

namespace App\Repository\Debts;

use App\Entity\Debts\Credits;
use App\Entity\Debts\CreditsBalance;
use App\Entity\Security\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;


class CreditRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
          parent::__construct($registry, Credits::class);
    }

    public function getActualCreditsByUser(User $user)
    {
        return $this->createQueryBuilder('d')
            ->leftJoin(CreditsBalance::class, 'db')
            ->where('d.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param User $user
     * @return mixed
     */
    public function getNextCreditsByUser(User $user)
    {
        $qb = $this->createQueryBuilder('c')
            ->join('c.balance', 'b')
            ->where('c.user = :user')
            ->andWhere('b.lastPayedMonth <> :actual_month')
            ->andWhere('b.status NOT IN (:status_invalid, :status_payed)')
            ->andWhere('b.balance > :zero')
            ->setParameters([
                'user' => $user,
                'actual_month' => $this->getActualMonth(),
                'status_invalid' => CreditsBalance::INVALID,
                'status_payed' => CreditsBalance::PAYED,
                'zero' => 0
            ])
        ;

        return $qb->getQuery()->getResult();
    }

    public function getNextPaymentDay()
    {

    }

    /**
     * @return false|string
     */
    protected function getActualMonth()
    {
        return date('Y-m');
    }

}
