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
     * Initial Amount - Amount payed
     *
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
     * Return how many dues are pending to Pay
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
        $capitalAmount = $this->calculator->calculateCapitalAmount(
            $this->extractActualDebt($creditCardConsume),
            $this->extractPendingDues($creditCardConsume)
        );

        $actualDue = $this->extractActualDueToPay($creditCardConsume);
        $lastPayedDue = $creditCardConsume->getDuesPayed();

        $pendingDues = $actualDue - $lastPayedDue;

        return $capitalAmount * $pendingDues;
    }

    /**
     * @param CreditCardConsume $creditCardConsume
     * @return float
     * @throws Exception
     */
    public function extractNextInterestAmount(CreditCardConsume $creditCardConsume): float
    {
        $actualDueToPay = $this->extractActualDueToPay($creditCardConsume);
        $duesPayed = $creditCardConsume->getDuesPayed();
        $interest = 0;

        if ($actualDueToPay <= $duesPayed) {
            return $interest;
        }

        $actualDebt = $this->extractActualDebt($creditCardConsume);
        $capitalAmount = $this->calculator->calculateCapitalAmount(
            $actualDebt,
            $this->extractPendingDues($creditCardConsume)
        );
        foreach (range($duesPayed + 1, $actualDueToPay) as $item){
            $interest += $this->calculator->calculateInterestAmount(
                $actualDebt,
                $creditCardConsume->getInterest()
            );
            $actualDebt -= $capitalAmount;
        }

        return $interest;
    }

    /**
     * Return what have to pay in the large of time
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

    /**
     * @param CreditCard $card
     * @param null $month
     * @return float
     * @throws Exception
     */
    public function extractTotalToPayByCreditCard(CreditCard $card, $month = null): float
    {
        $consumes = $this->consumeProvider->getByCreditCard($card, $month);
        return $this->sumConsumes($consumes);
    }

    /**
     * @param CreditCardUser $cardUser
     * @param CreditCard|null $card
     * @param null $month
     * @return float
     * @throws Exception
     */
    public function extractTotalToPayByCardUser(CreditCardUser $cardUser, CreditCard $card = null, $month = null): float
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
     * @param CreditCardConsume[] $cardConsumes
     * @return array
     * @throws Exception
     */
    public function extractConsumeResume(array $cardConsumes): array
    {
        $arrayConsumes = [];

        foreach ($cardConsumes as $consume){
            $arrayConsumes[] = $this->resume($consume);
        }

        return $arrayConsumes;
    }

    /**
     * @param CreditCardConsume[] $cardConsumes
     * @param null $groupBy
     * @return array
     * @throws Exception
     */
    public function extractListGroupedBy($cardConsumes, $groupBy = null)
    {
        $arrayConsumes = [];
        foreach ($cardConsumes as $consume){
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
     * @throws Exception
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
            'pending_payments' => $this->extractPendingPaymentsByConsume($consume),
            'status' => $consume->getStatus(),
            'mora' => 0 // todo: definir deudas
        ];

        return $consumeArray;
    }

    /**
     * @param array $consumes
     * @return float
     * @throws Exception
     */
    private function sumConsumes(array $consumes): float
    {
        $total = 0;
        foreach ($consumes as $consume) {
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
        $dateList = [];
        foreach ($this->paymentsRepository->getMonthListByConsume($cardConsume) as $date){
            $dateList[] = array_values($date);
        }

        return $this->calculator->calculateMajorMonth(
            $dateList
        );
    }

    /**
     * @param CreditCardConsume $creditCardConsume
     * @return int
     * @throws Exception
     */
    public function extractActualDueToPay(CreditCardConsume $creditCardConsume): int
    {
        return $this->calculator->calculateActualDueToPay(
            $creditCardConsume->getDuesPayed(),
            $this->extractLastPaymentMonth($creditCardConsume),
            $this->calculator->calculateNextPaymentDate()
        );
    }

}