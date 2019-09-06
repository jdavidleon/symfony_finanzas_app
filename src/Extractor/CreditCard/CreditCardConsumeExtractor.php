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
use App\Repository\CreditCard\CreditCardPaymentsRepository;
use App\Service\CreditCard\CreditCalculator;
use App\Service\CreditCard\CreditCardConsumeProvider;
use Exception;


class CreditCardConsumeExtractor
{
    /**
     * @var CreditCardConsumeProvider
     */
    private $consumeProvider;
    /**
     * @var CreditCalculator
     */
    private $calculator;
    /**
     * @var CreditCardPaymentsRepository
     */
    private $paymentsRepository;

    public function __construct(
        CreditCardConsumeProvider $consumeProvider,
        CreditCardPaymentsRepository $paymentsRepository,
        CreditCalculator $calculations
    )
    {
        $this->consumeProvider = $consumeProvider;
        $this->calculator = $calculations;
        $this->paymentsRepository = $paymentsRepository;
    }

    /**
     * @param CreditCardConsume $creditCardConsume
     * @return float
     */
    public function extractActualDebt(CreditCardConsume $creditCardConsume): float
    {
        return $this->calculator->calculateActualCreditCardConsumeDebt(
            $creditCardConsume->getAmount(),
            $creditCardConsume->getAmountPayed()
        );
    }

    /**
     * @param CreditCardConsume $creditCardConsume
     * @return int|null
     */
    public function extractPendingDues(CreditCardConsume $creditCardConsume): int
    {
        return $this->calculator->calculateNumberOfPendingDues(
            $creditCardConsume->getDues(),
            $creditCardConsume->getDuesPayed()
        );
    }

    /**
     * @param CreditCardConsume $creditCardConsume
     * @return float
     * @throws Exception
     */
    public function extractNextCapitalAmount(CreditCardConsume $creditCardConsume): float
    {
        $actualDue = $this->extractActualDueToPay($creditCardConsume);
        $lastPayedDue = $creditCardConsume->getDuesPayed();

        $pendingDues = $actualDue - $lastPayedDue;

        return $this->calculator->calculateCapitalAmount(
                $this->extractActualDebt($creditCardConsume),
                $this->extractPendingDues($creditCardConsume)
            ) * $pendingDues;
    }

    public function extractNextInterestAmount(CreditCardConsume $creditCardConsume)
    {
        return $this->calculator->calculateNextInterestAmount(
            $this->extractActualDebt($creditCardConsume),
            $creditCardConsume->getInterest()
        );
    }

    /**
     * Return what have to pay in large of time
     * @param CreditCardConsume $creditCardConsume
     * @return float|int|null
     * @throws Exception
     */
    public function extractNextPaymentAmount(CreditCardConsume $creditCardConsume)
    {
        return $this->calculator->calculateNextPaymentAmount(
            $this->extractNextCapitalAmount($creditCardConsume),
            $this->extractNextInterestAmount($creditCardConsume)
        );
    }

    /**
     * @param CreditCardConsume $creditCardConsume
     * @return array
     * @throws Exception
     */
    public function extractPendingPaymentsByConsume(CreditCardConsume $creditCardConsume)
    {
        return $this->calculator->calculatePendingPaymentsResume(
            $this->extractActualDebt($creditCardConsume),
            $creditCardConsume->getInterest(),
            $creditCardConsume->getDues(),
            $creditCardConsume->getDuesPayed(),
            $this->extractLastPaymentMonth($creditCardConsume)
        );
    }

    /**
     * @param CreditCardConsume $creditCardConsume
     * @return int|null
     */
    public function getActualDueToPay(CreditCardConsume $creditCardConsume)
    {
        return $this->calculator->calculateNextDueToPay(
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

    /**
     * @param User $owner
     * @return float|int|null
     * @throws Exception
     */
    public function extractTotalToPayByOwner(User $owner)
    {
        $consumes = $this->consumeProvider->getByOwner($owner, $this->extractNextPaymentMonth());
        return $this->sumConsumes($consumes);
    }

    /**
     * @param CreditCardConsume[] $cardConsume
     * @return array
     */
    public function extractConsumeListBy($cardConsume)
    {
        $arrayConsumes = [];

        foreach ($cardConsume as $consume){
            $arrayConsumes[] = $this->resume($consume);
        }

        return $arrayConsumes;
    }

    public function extractListGroupedBy($cardConsume, $groupBy = null)
    {
        $arrayConsumes = [];
        foreach ($cardConsume as $consume){
            switch ($groupBy){
                case 'user':
                    $key = $consume->getCreditCardUser()->getId();
                    break;
                case 'credit_card':
                    $key = $consume->getCreditCard()->getId();
                    break;
                default:
                    $key = $consume->getCreditCardUser()->getId();
            }
            $arrayConsumes[$key][] = $this->resume($consume);
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
            'consume_id' => $consume->getId(),
            'user_id' => $consume->getCreditCardUser()->getId(),
            'user_name' => $consume->getCreditCardUser()->getFullName(),
            'user_alias' => $consume->getCreditCardUser()->getAlias(),
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
            'payments' => $consume->getPayments(),
            'mora' => 0
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
            // TODO: Ajustar método para llamar las deudas pendientes incluyendo moras
            $total += $this->extractNextPaymentAmount($consume);
        }
        return $total;
    }

    /**
     * @param CreditCardConsume|null $cardConsume
     * @return string
     * @throws Exception
     */
    public function extractNextPaymentMonth(?CreditCardConsume $cardConsume = null): string
    {
        if ($cardConsume instanceof CreditCardConsume) {
            if (!$cardConsume->hasPayments()){
                return $cardConsume->getMonthFirstPay();
            }else {
                $date = $this->getCalculateMajorMonth($cardConsume);
            }
        }else {
            $date = null;
        }

        return $this->calculator->calculateNextPaymentDate($date);
    }

    /**
     * @param CreditCardConsume $cardConsume
     * @return string
     * @throws Exception
     */
    public function extractLastPaymentMonth(CreditCardConsume $cardConsume): string
    {
        if ($cardConsume->hasPayments()){
            return $this->getCalculateMajorMonth($cardConsume);
        }else {
            return $this->calculator->reverseMonth($cardConsume->getMonthFirstPay());
        }
    }

    /**
     * @param CreditCardConsume|null $cardConsume
     * @return string
     */
    private function getCalculateMajorMonth(?CreditCardConsume $cardConsume): string
    {
        return $this->calculator->calculateMajorMonth(
            $this->paymentsRepository->getMonthListByConsume($cardConsume)
        );
    }

    /**
     * @param CreditCardConsume $creditCardConsume
     * @return \DateTime|int
     * @throws Exception
     */
    public function extractActualDueToPay(CreditCardConsume $creditCardConsume)
    {
        return $this->calculator->calculateActualDueToPay(
            $creditCardConsume->getDuesPayed(),
            $this->extractLastPaymentMonth($creditCardConsume),
            $this->calculator->calculateNextPaymentDate()
        );
    }

}