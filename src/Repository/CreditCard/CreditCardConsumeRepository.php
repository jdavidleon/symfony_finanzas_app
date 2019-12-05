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
    public function getByCreditCard(CreditCard $creditCard, string $month = null): array
    {
        $qb = $this->createQueryBuilder('ccc')
            ->where('ccc.creditCard = :credit_card')
            ->andWhere('ccc.deletedAt IS NULL')
            ->andWhere('ccc.status IN ( :paying )')
            ->setParameters([
                'credit_card' => $creditCard,
                'paying' => [
                    CreditCardConsume::STATUS_PAYING,
                    CreditCardConsume::STATUS_MORA
                ]
            ]);

        $this->addPayedMonthConditional($qb, $month);

        return $qb
            ->getQuery()
            ->getResult();
    }

    public function getActivesByOwner(User $owner, $month = null)
    {
        $qb = $this->createQueryBuilder('ccc')
            ->join('ccc.creditCard', 'creditCard')
            ->join('ccc.creditCardUser', 'creditCardUser')
            ->join('creditCard.owner', 'owner')
            ->where('owner = :owner')
            ->andWhere('ccc.deletedAt IS  NULL')
            ->andWhere('ccc.status IN ( :paying )')
            ->setParameters([
                'owner' => $owner,
                'paying' => [
                    CreditCardConsume::STATUS_PAYING,
                    CreditCardConsume::STATUS_MORA
                ]
            ]);

        $this->addPayedMonthConditional($qb, $month);

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
    public function getActivesByCardUser(CreditCardUser $cardUser, CreditCard $card = null, $month = null)
    {
        $qb = $this->createQueryBuilder('ccc')
            ->andWhere('ccc.creditCardUser = :card_user')
            ->andWhere('ccc.deletedAt IS NULL')
            ->andWhere('ccc.status IN (:paying)')
            ->setParameters([
                'card_user' => $cardUser,
                'paying' => [
                    CreditCardConsume::STATUS_PAYING,
                    CreditCardConsume::STATUS_MORA
                ]
            ]);

        if ( null != $card ){
            $qb
                ->andWhere('ccc.creditCard = :card')
                ->setParameter('card', $card);
        }

        $this->addPayedMonthConditional($qb, $month);

        return $qb->getQuery()
            ->getResult()
            ;
    }

    /**
     * @param CreditCard $card
     * @param CreditCardUser $cardUser
     * @return CreditCardConsume[]
     */
    public function getByCardAndUser(CreditCard $card, CreditCardUser $cardUser)
    {
        return $this->createQueryBuilder('ccc')
            ->where('ccc.creditCardUser = :user')
            ->andWhere('ccc.creditCard = :card')
            ->andWhere('ccc.deletedAt IS NULL')
            ->andWhere('ccc.status IN (:statuses)')
            ->setParameters([
                'card' => $card,
                'user' => $cardUser,
                'statuses' => [
                    CreditCardConsume::STATUS_PAYING,
                    CreditCardConsume::STATUS_MORA,
                ],
            ])
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * Retorna los consumos con status creado
     * @param User $owner
     * @return CreditCardConsume[]
     */
    public function findCreatedConsumeListByOwner(User $owner)
    {
        return $this->createQueryBuilder('ccc')
            ->join('ccc.creditCard', 'card')
            ->where('ccc.status = :status_created')
            ->andWhere('card.owner = :owner')
            ->andWhere('ccc.deletedAt IS NULL')
            ->setParameters([
                'status_created' => CreditCardConsume::STATUS_CREATED,
                'owner' => $owner
            ])
            ->getQuery()
            ->getResult();
    }

    /**
     * @param QueryBuilder $qb
     * @param string $month
     * @return void
     */
    private function addPayedMonthConditional(QueryBuilder $qb, string $month = null)
    {
        if ( null != $month ) {
            $qb
                ->andWhere(
                    $qb->expr()->not(
                        $qb->expr()->exists('
                            SELECT ccp
                        FROM \App\Entity\CreditCard\CreditCardPayment ccp
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
    }

}
