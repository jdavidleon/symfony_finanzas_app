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
     * @param int $comingDays
     * @return mixed
     * @throws \Exception
     */
    public function getNextCreditsByUserByComingDays(User $user, int $comingDays = 15)
    {
        $today = new \DateTime();
        $comingDate = new \DateTime('+'.$comingDays.' Days');

        $qb = $this->createQueryBuilder('c')
            ->join('c.balance', 'b')
            ->where('c.user = :user');
        $qb->andWhere($qb->expr()->orX(
            'c.paymentDay BETWEEN :today AND :coming',
                'b.'
        ))
            ->andWhere('b.status NOT IN (:status_invalid, :status_payed)')
            ->setParameters([
                'user' => $user,
                'today' => $today,
                'coming' => $comingDate,
                'status_invalid' => CreditsBalance::INVALID,
                'status_payed' => CreditsBalance::PAYED
            ])
        ;

        return $qb->getQuery()->getResult();
    }

}
