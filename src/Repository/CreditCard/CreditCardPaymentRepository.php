<?php

namespace App\Repository\CreditCard;

use App\Entity\CreditCard\CreditCardConsume;
use App\Entity\CreditCard\CreditCardPayment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CreditCardPayment|null find($id, $lockMode = null, $lockVersion = null)
 * @method CreditCardPayment|null findOneBy(array $criteria, array $orderBy = null)
 * @method CreditCardPayment[]    findAll()
 * @method CreditCardPayment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CreditCardPaymentRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CreditCardPayment::class);
    }

    /**
     * @param CreditCardConsume $consume
     * @param bool $legalDues
     * @return CreditCardPayment[]
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
