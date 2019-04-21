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
use App\Repository\CreditCard\CreditCardConsumeRepository;
use App\Service\CreditCard\CreditCalculations;


class CreditCardConsumeExtractor
{
    /**
     * @var CreditCardConsumeRepository
     */
    private $cardConsumeRepository;
    /**
     * @var CreditCalculations
     */
    private $calculations;

    public function __construct(
        CreditCardConsumeRepository $cardConsumeRepository,
        CreditCalculations $calculations
    )
    {
        $this->cardConsumeRepository = $cardConsumeRepository;
        $this->calculations = $calculations;
    }

    /**
     * @param CreditCardConsume $creditCardConsume
     * @return int
     */
    public function extractActualDebt(CreditCardConsume $creditCardConsume): int
    {
        $pays = $creditCardConsume->getPayments();

        $payments = [];
        foreach ($pays as $pay) {
            $payments[] = $pay->getCapitalAmount();
        }

        return $this->calculations->calculateActualCreditCardConsumeDebt($creditCardConsume->getAmount(), $payments);
    }

    /**
     * @param CreditCardConsume $creditCardConsume
     * @return float|int|null
     */
    public function extractNextCapitalAmount(CreditCardConsume $creditCardConsume)
    {
        return $this->calculations->calculateNextCapitalAmount(
            $this->extractActualDebt($creditCardConsume),
            $this->getPendingDues($creditCardConsume)
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
    public function getPendingDues(CreditCardConsume $creditCardConsume)
    {
        return $this->calculations->calculateNumberOfPendingDues(
            $creditCardConsume->getDues(),
            count( $this->cardConsumeRepository->getDuesPayments( $creditCardConsume->getCreditCardUser() ) )
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
            $this->getPendingDues( $creditCardConsume ),
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
            $this->getPendingDues($creditCardConsume)
        );
    }

    public function getNumberOfPendingDues(CreditCardConsume $creditCardConsume)
    {
        return $this->calculations->calculateNumberOfPendingDues(
            $creditCardConsume->getDues(),
            $this->getPendingDues($creditCardConsume)
        );
    }

    /**
     * @param User $owner
     * @return array
     */
    public function extractByOwner(User $owner)
    {
        return $this->cardConsumeRepository->getByOwner($owner);
    }

    public function extractByCreditCard(CreditCard $card)
    {
       return $this->cardConsumeRepository->getCreditConsumesByCreditCard($card);
    }

    public function extractTotalToPayByCreditCard(CreditCard $card)
    {
        $consumes = $this->cardConsumeRepository->getCreditConsumesByCreditCard($card);
        return $this->sumConsumes($consumes);
    }

    public function extractTotalToPayByCreditCardUserAndCard(CreditCardUser $cardUser, CreditCard $card = null)
    {
        $consumes = $this->cardConsumeRepository->getCreditCardConsumeByCreditCardUserAndCard($cardUser, $card);
        return $this->sumConsumes($consumes);
    }

    public function extractTotalToPayByOwner(User $owner)
    {
        $consumes = $this->extractByOwner($owner);
        return $this->sumConsumes($consumes);
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

    /**
     * @param array $debtsByUser
     * @return array
     */
//    public function getDebtsByUserInCreditCard(Array $debtsByUser)
//    {
//        $userDebt = [];
//        foreach ($debtsByUser as $key => $userDebts ){
//            $total = $capital = $interest = 0;
//            foreach ( $userDebts as $debt ){
//                $capital += $debt['capital_payment'];
//                $interest += $debt['interest'];
//                $total += $debt['total'];
//            }
//            $userDebt[$key] = array(
//                'user' => $userDebts[0]['user'],
//                'total_capital' => $capital,
//                'total_interest' => $interest,
//                'total_payment' => $total
//            );
//        }
//
//        return $userDebt;
//    }

}