<?php

namespace App\Repository\CreditCard;

use App\Entity\CreditCard\CreditCard;
use App\Entity\CreditCard\CreditCardConsume;
use App\Entity\CreditCard\CreditCardUser;
use App\Entity\Security\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
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

    /**
     * @param CreditCard $creditCard
     * @param string|null $month
     * @return CreditCardConsume[]
     */
    public function getCreditConsumesByCreditCard(CreditCard $creditCard, string $month = null)
    {
        $qb = $this->createQueryBuilder('ccc')
            ->where('ccc.creditCard = :credit_card')
            ->andWhere('ccc.delete_at IS NULL')
            ->andWhere('ccc.status IN ( :paying )')
            ->setParameters([
                'credit_card' => $creditCard,
                'paying' => [
                    CreditCardConsume::STATUS_PAYING,
                    CreditCardConsume::STATUS_MORA
                ]
            ]);

        $qb = $this->addPayedMonthConditional($qb, $month);

        return $qb
            ->getQuery()
            ->getResult();
    }

    public function getByOwner(User $owner, $month = null)
    {
        $qb = $this->createQueryBuilder('ccc')
            ->join('ccc.creditCard', 'creditCard')
            ->join('ccc.creditCardUser', 'creditCardUser')
            ->join('creditCard.owner', 'owner')
            ->where('owner = :owner')
            ->andWhere('ccc.delete_at IS  NULL')
            ->andWhere('ccc.status <> :payed_status')
            ->setParameters([
                'owner' => $owner,
                'payed_status' => CreditCardConsume::STATUS_PAYED
            ]);

        $qb = $this->addPayedMonthConditional($qb, $month);

        return $qb
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @param CreditCardUser $cardUser
     * @param CreditCard $card
     * @param null $month
     * @return CreditCardConsume[]
     */
    public function getByCardUserAndCard(CreditCardUser $cardUser, CreditCard $card = null, $month = null)
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

        $qb = $this->addPayedMonthConditional($qb, $month);

        return $qb->getQuery()
            ->getResult()
            ;
    }

    /**
     * @param string $month
     * @param QueryBuilder $qb
     * @return QueryBuilder
     */
    private function addPayedMonthConditional(QueryBuilder $qb, string $month = null): QueryBuilder
    {
        if ( null != $month ) {
            $qb
                ->andWhere(
                    $qb->expr()->not(
                        $qb->expr()->exists('
                            SELECT ccp
                        FROM \App\Entity\CreditCard\CreditCardPayments ccp
                        WHERE
                        ccp.creditConsume = ccc.id
                        AND
                        ccp.monthPayed = :month
                        AND 
                        ccp.deletedAt IS NULL
                            ')
                    )
                )
                ->setParameter('month', $month);
        }
        return $qb;
    }

}
