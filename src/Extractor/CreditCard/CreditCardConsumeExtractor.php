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
use App\Entity\CreditCard\Model\ConsumePaymentResume;
use App\Entity\Model\CardConsumeResume;
use App\Entity\Security\User;
use App\Repository\CreditCard\CreditCardPaymentRepository;
use App\Service\CreditCard\CreditCalculator;
use App\Service\CreditCard\CreditCardConsumeProvider;
use App\Service\DateHelper;
use Exception;


class CreditCardConsumeExtractor
{
    /**
     * @var CreditCardConsumeProvider
     */
    private $consumeProvider;
    
    /**
     * @var CreditCardPaymentRepository
     */
    private $paymentsRepository;

    public function __construct(
        CreditCardConsumeProvider $consumeProvider,
        CreditCardPaymentRepository $paymentsRepository
    )
    {
        $this->consumeProvider = $consumeProvider;
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
        return CreditCalculator::calculateActualCreditCardConsumeDebt(
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
        return CreditCalculator::calculateNumberOfPendingDues(
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
        $capitalAmount = CreditCalculator::calculateCapitalAmount(
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
        $capitalAmount = CreditCalculator::calculateCapitalAmount(
            $actualDebt,
            $this->extractPendingDues($creditCardConsume)
        );
        foreach (range($duesPayed + 1, $actualDueToPay) as $item){
            $interest += CreditCalculator::calculateInterestAmount(
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
        return CreditCalculator::calculateNextPaymentAmount(
            $this->extractNextCapitalAmount($creditCardConsume),
            $this->extractNextInterestAmount($creditCardConsume)
        );
    }

    /**
     * @param CreditCardConsume $creditCardConsume
     * @param bool $atDate
     * @return ConsumePaymentResume[]
     * @throws Exception
     */
    public function extractPendingPaymentsByConsume(CreditCardConsume $creditCardConsume, bool $atDate = false): array
    {
        return CreditCalculator::calculatePendingPaymentsResume(
            $this->extractActualDebt($creditCardConsume),
            $creditCardConsume->getInterest(),
            $creditCardConsume->getDues(),
            $creditCardConsume->getDuesPayed(),
            $atDate ? $this->extractActualDueToPay($creditCardConsume): null,
            $this->extractLastPaymentMonth($creditCardConsume)
        );
    }

    /**
     * @param CreditCardConsume $creditCardConsume
     * @return int|null
     */
    public function getActualDueToPay(CreditCardConsume $creditCardConsume)
    {
        return CreditCalculator::calculateNextDueToPay(
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
     * @return CardConsumeResume[]
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
//    public function extractListGroupedBy($cardConsumes, $groupBy = null)
//    {
//        $arrayConsumes = [];
//        foreach ($cardConsumes as $consume){
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
//            $arrayConsumes[$key][] = $this->resume($consume);
//        }
//
//        return $arrayConsumes;
//    }

    /**
     * @param CreditCardConsume $consume
     * @return CardConsumeResume
     * @throws Exception
     */
    private function resume(CreditCardConsume $consume): CardConsumeResume
    {
        return new CardConsumeResume(
            $consume,
            $this->extractPendingDues($consume),
            $this->extractNextCapitalAmount($consume),
            $this->extractNextInterestAmount($consume),
            $this->extractNextPaymentAmount($consume),
            $this->extractActualDebt($consume),
            $this->extractPendingPaymentsByConsume($consume)
        );
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

        return CreditCalculator::calculateNextPaymentDate($date);
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
            return DateHelper::reverseMonth($cardConsume->getMonthFirstPay());
        }
    }

    /**
     * @param CreditCardConsume|null $cardConsume
     * @return string
     */
    private function getCalculateMajorMonth(?CreditCardConsume $cardConsume): string
    {
        $dateList = [];
        // Todo: mejorar el retorno de month
        foreach ($this->paymentsRepository->getMonthListByConsume($cardConsume) as $arrayDate){
            foreach ($arrayDate as $date){
                $dateList[] = $date;
            }
        }

        return DateHelper::calculateMajorMonth(
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
        return CreditCalculator::calculateActualDueToPay(
            $creditCardConsume->getDuesPayed(),
            $this->extractLastPaymentMonth($creditCardConsume),
            CreditCalculator::calculateNextPaymentDate()
        );
    }

}