<?php

namespace App\Repository\CreditCard;

use App\Entity\CreditCard\CreditCardConsume;
use App\Entity\CreditCard\CreditCardPayments;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CreditCardPayments|null find($id, $lockMode = null, $lockVersion = null)
 * @method CreditCardPayments|null findOneBy(array $criteria, array $orderBy = null)
 * @method CreditCardPayments[]    findAll()
 * @method CreditCardPayments[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CreditCardPaymentsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CreditCardPayments::class);
    }

    /**
     * @param CreditCardConsume $consume
     * @param bool $legalDues
     * @return CreditCardPayments[]
     */
    public function getByConsume(CreditCardConsume $consume, $legalDues = false)
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.creditConsume = :consume')
            ->andWhere('p.deletedAt IS NULL')
            ->setParameters([
                'consume' => $consume
            ]);

        if ($legalDues) {
            $qb
                ->andWhere('p.legalDue = true');
        }

        return $qb
            ->getQuery()
            ->getResult();
    }

    public function getMonthListByConsume(CreditCardConsume $cardConsume)
    {
        return $this->createQueryBuilder('p')
            ->select('p.monthPayed')
            ->where('p.creditConsume = :consume')
            ->andWhere('p.legalDue = true')
            ->setParameter('consume', $cardConsume)
            ->getQuery()
            ->getArrayResult();
    }
}
