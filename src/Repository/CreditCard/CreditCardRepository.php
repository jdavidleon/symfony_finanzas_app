<?php

namespace App\Repository\CreditCard;

use App\Entity\CreditCard\CreditCard;
use App\Entity\Security\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CreditCard|null find($id, $lockMode = null, $lockVersion = null)
 * @method CreditCard|null findOneBy(array $criteria, array $orderBy = null)
 * @method CreditCard[]    findAll()
 * @method CreditCard[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CreditCardRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CreditCard::class);
    }

    public function getDebtsByCreditCard(CreditCard $creditCard)
    {
        return $this->createQueryBuilder('cc')
            ->where('cc = :credit_card')
            ->andWhere('cc.deletedAt IS NULL')
            ->setParameters([
                'credit_card' => $creditCard
            ])
            ->getQuery()
            ->getResult();
    }

    /**
     * @param User $owner
     * @return QueryBuilder
     */
    public function getByOwnerQB(User $owner)
    {
        return $this->createQueryBuilder('cc')
            ->where('cc.owner = :owner')
            ->andWhere('cc.deletedAt IS NULL')
            ->setParameters([
                'owner' => $owner
            ])
            ;
    }

    // /**
    //  * @return CreditCard[] Returns an array of CreditCard objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CreditCard
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
