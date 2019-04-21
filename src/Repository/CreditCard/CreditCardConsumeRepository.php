<?php

namespace App\Repository\CreditCard;

use App\Entity\CreditCard\CreditCard;
use App\Entity\CreditCard\CreditCardConsume;
use App\Entity\CreditCard\CreditCardUser;
use App\Entity\Security\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CreditCardConsume|null find($id, $lockMode = null, $lockVersion = null)
 * @method CreditCardConsume|null findOneBy(array $criteria, array $orderBy = null)
 * @method CreditCardConsume[]    findAll()
 * @method CreditCardConsume[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method findByUser(int $int)
 * @method findByCreditCard($creditCard)
 */
class CreditCardConsumeRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CreditCardConsume::class);
    }


    public function getCreditConsumesByCreditCard(CreditCard $creditCard)
    {
        return $this->createQueryBuilder('ccc')
            ->join('ccc.creditCardUser', 'creditCardUser')
            ->where('ccc.creditCard = :credit_card')
            ->andWhere('ccc.delete_at IS NULL')
            ->andWhere('ccc.status <> :payed')
            ->setParameters([
                'credit_card' => $creditCard,
                'payed' => CreditCardConsume::STATUS_PAYED
            ])
            ->getQuery()
            ->getResult()
            ;
    }

    public function getByOwner(User $owner)
    {
        return $this->createQueryBuilder('ccc')
            ->join('ccc.creditCard', 'creditCard')
            ->join('ccc.creditCardUser', 'creditCardUser')
            ->join('creditCard.owner', 'owner')
            ->where('owner = :owner')
            ->andWhere('ccc.delete_at IS  NULL')
            ->andWhere('ccc.status <> :payed_status')
            ->setParameters([
                'owner' => $owner,
                'payed_status' => CreditCardConsume::STATUS_PAYED
            ])
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @param CreditCardUser $creditCardUser
     * @return mixed
     */
    public function getDuesPayments(CreditCardUser $creditCardUser)
    {
        return $this->createQueryBuilder('ccc')
            ->select('p.id')
            ->leftJoin('ccc.payments', 'p')
            ->where('ccc.creditCardUser = :credit_card_user')
            ->andWhere('p.legalDue = true')
            ->setParameter('credit_card_user', $creditCardUser)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param CreditCard $card
     * @param CreditCardUser $cardUser
     * @return CreditCardConsume[]
     */
    public function getCreditCardConsumeByCreditCardUserAndCard(CreditCardUser $cardUser, CreditCard $card = null)
    {
        $qb = $this->createQueryBuilder('ccc')
            ->andWhere('ccc.creditCardUser = :card_user')
            ->andWhere('ccc.delete_at IS NULL')
            ->andWhere('ccc.status <> :payed_status')
            ->setParameters([
                'card_user' => $cardUser,
                'payed_status' => CreditCardConsume::STATUS_PAYED
            ]);

        if ( null != $card ){
            $qb
                ->andWhere('ccc.creditCard = :card')
                ->setParameter('card', $card);
        }

        return $qb->getQuery()
            ->getResult()
            ;
    }

//    public function getDebtPaymentsByConsume($consume)
//    {
//        return $this->createQueryBuilder('c')
//            ->join('c.payments', 'cp')
//            ->where('c.id = :consume')
//            ->set
//    }

    // /**
    //  * @return CreditCardConsume[] Returns an array of CreditCardConsume objects
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
    public function findOneBySomeField($value): ?CreditCardConsume
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
