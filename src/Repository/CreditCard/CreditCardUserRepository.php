<?php

namespace App\Repository\CreditCard;

use App\Entity\CreditCard\CreditCard;
use App\Entity\CreditCard\CreditCardConsume;
use App\Entity\CreditCard\CreditCardUser;
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

    public function getByOwnerQB($owner)
    {
        return $this->createQueryBuilder('ccu')
            ->where('ccu.parent = :parent')
            ->andWhere('ccu.deletedAt IS NULL')
            ->setParameter('parent', $owner)
            ;
    }

    public function getByOwner($owner, $activeDebts = false)
    {
        $qb = $this->getByOwnerQB($owner);

        if ($activeDebts){
            $qb
                ->join('ccu.creditCardConsume', 'ccc')
                ;
        }

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @param CreditCard $card
     * @return CreditCardUser[]
     */
    public function getByCreditCard(CreditCard $card)
    {
        return $this->createQueryBuilder('ccu')
            ->join('ccu.creditCardConsume', 'ccc')
            ->join('ccc.creditCard', 'cc')
            ->where('cc = :credit_card')
            ->andWhere('ccc.status <> :payed')
            ->andWhere('ccc.deletedAt IS NULL')
            ->setParameters([
                'credit_card' => $card,
                'payed' => CreditCardConsume::STATUS_PAYED
            ])
            ->getQuery()
            ->getResult();
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
