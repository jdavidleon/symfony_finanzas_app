<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 7/04/2019
 * Time: 12:27 PM
 */

namespace App\Extractor\CreditCard;

use App\Entity\CreditCard\CreditCard;
use App\Entity\CreditCard\CreditCardConsume;
use App\Entity\CreditCard\CreditCardUser;
use App\Entity\Security\User;
use App\Repository\CreditCard\PaymentsRepository;
use App\Service\CreditCard\CreditCalculations;
use App\Service\CreditCard\CreditCardConsumeProvider;


class CreditCardConsumeExtractor
{
    /**
     * @var CreditCardConsumeProvider
     */
    private $consumeProvider;
    /**
     * @var CreditCalculations
     */
    private $calculations;
    /**
     * @var PaymentsRepository
     */
    private $paymentsRepository;

    public function __construct(
        CreditCardConsumeProvider $consumeProvider,
        PaymentsRepository $paymentsRepository,
        CreditCalculations $calculations
    )
    {
        $this->consumeProvider = $consumeProvider;
        $this->calculations = $calculations;
        $this->paymentsRepository = $paymentsRepository;
    }

    /**
     * @param CreditCardConsume $creditCardConsume
     * @return int
     */
    public function extractActualDebt(CreditCardConsume $creditCardConsume): int
    {
        return $this->calculations->calculateActualCreditCardConsumeDebt(
            $creditCardConsume->getAmount(),
            $creditCardConsume->getAmountPayed()
        );
    }

    /**
     * @param CreditCardConsume $creditCardConsume
     * @return float|int|null
     */
    public function extractNextCapitalAmount(CreditCardConsume $creditCardConsume)
    {
        return $this->calculations->calculateNextCapitalAmount(
            $this->extractActualDebt($creditCardConsume),
            $this->extractPendingDues($creditCardConsume)
        );
    }

    public function extractNextInterestAmount(CreditCardConsume $creditCardConsume)
    {
        return $this->calculations->calculateNextInterestAmount(
            $this->extractActualDebt($creditCardConsume),
            $creditCardConsume->getInterest()
        );
    }

    /**
     * Return what have to pay Capital + Interest
     * @param CreditCardConsume $creditCardConsume
     * @return float|int|null
     */
    public function extractNextPaymentAmount(CreditCardConsume $creditCardConsume)
    {
        return $this->calculations->calculateNextPaymentAmount(
            $this->extractNextCapitalAmount($creditCardConsume),
            $this->extractNextInterestAmount($creditCardConsume)
        );
    }


    /**
     * @param CreditCardConsume $creditCardConsume
     * @return int|null
     */
    public function extractPendingDues(CreditCardConsume $creditCardConsume)
    {
        return $this->calculations->calculateNumberOfPendingDues(
            $creditCardConsume->getDues(),
            $creditCardConsume->getDuesPayed()
        );
    }

    /**
     * @param CreditCardConsume $creditCardConsume
     * @return array
     */
    public function extractPendingPaymentsByConsume(CreditCardConsume $creditCardConsume)
    {
        return $this->calculations->calculatePendingPayments(
            $this->extractActualDebt($creditCardConsume),
            $creditCardConsume->getInterest(),
            $this->extractPendingDues( $creditCardConsume ),
            $this->getActualDueToPay( $creditCardConsume )
        );
    }

    /**
     * @param CreditCardConsume $creditCardConsume
     * @return int|null
     */
    public function getActualDueToPay(CreditCardConsume $creditCardConsume)
    {
        return $this->calculations->calculateActualDueToPay(
            $creditCardConsume->getDues(),
            $this->extractPendingDues($creditCardConsume)
        );
    }

    public function getNumberOfPendingDues(CreditCardConsume $creditCardConsume)
    {
        return $this->calculations->calculateNumberOfPendingDues(
            $creditCardConsume->getDues(),
            $this->extractPendingDues($creditCardConsume)
        );
    }

    public function extractTotalToPayByCreditCard(CreditCard $card, $month = null)
    {
        $consumes = $this->consumeProvider->getByCreditCard($card, $month);
        return $this->sumConsumes($consumes);
    }

    public function extractTotalToPayByCardUser(CreditCardUser $cardUser, CreditCard $card = null, $month = null)
    {
        $consumes = $this->consumeProvider->getByCardUser($cardUser, $card, $month);
        return $this->sumConsumes($consumes);
    }

    public function extractTotalToPayByOwner(User $owner)
    {
        $consumes = $this->consumeProvider->getByOwner($owner, $this->extractNextPaymentMonth());
        return $this->sumConsumes($consumes);
    }

    /**
     * @param CreditCardConsume[] $cardConsume
     * @param $groupBy
     * @return array
     */
    public function extractListConsumeBy($cardConsume, $groupBy = null)
    {
        $arrayConsumes = [];

        foreach ($cardConsume as $consume){
//            switch ($groupBy){
//                case 'user':
//                    $key = $consume->getCreditCardUser()->getId();
//                    break;
//                case 'credit_card':
//                    $key = $consume->getCreditCard()->getId();
//                    break;
//                default:
//                    $key = $consume->getCreditCardUser()->getId();
//            }
            $arrayConsumes[] = $this->resume($consume);
        }

        return $arrayConsumes;
    }

    /**
     * @param CreditCardConsume $consume
     * @return array
     */
    private function resume(CreditCardConsume $consume)
    {
        $consumeArray = [
            'user_id' => $consume->getCreditCardUser()->getId(),
            'user_name' => $consume->getCreditCardUser()->getFullName(),
            'user_alias' => $consume->getCreditCardUser()->getAlias(),
            'id' => $consume->getId(),
            'credit_card' => $consume->getCreditCard(),
            'description' => $consume->getDescription(),
            'amount' => $consume->getAmount(),
            'pending_amount' => $this->extractActualDebt($consume),
            'dues' => $consume->getDues(),
            'pending_dues' => $this->extractPendingDues($consume),
            'interest' => $consume->getInterest(),
            'capital_amount' => $this->extractNextCapitalAmount($consume),
            'interest_amount' => $this->extractNextInterestAmount($consume),
            'total_amount' => $this->extractNextPaymentAmount($consume),
        ];

        return $consumeArray;
    }

    /**
     * @param array $consumes
     * @return float|int|null
     */
    private function sumConsumes(array $consumes)
    {
        $total = 0;
        foreach ($consumes as $consume) {
            $total += $this->extractNextPaymentAmount($consume);
        }
        return $total;
    }

    public function extractNextPaymentMonth(): string
    {
        return $this->calculations->calculateNextPaymentDate();
    }

}