<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 7/04/2019
 * Time: 12:27 PM
 */

namespace App\Extractor\CreditCard;

use App\Entity\CreditCard\CreditCardConsume;
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
    public function getActualCreditCardDebt(CreditCardConsume $creditCardConsume): int
    {
        $pays = $creditCardConsume->getPayments();

        $payments = [];
        foreach ($pays as $pay) {
            $payments[] = $pay->getCapitalAmount();
        }

        return $this->calculations->calculateActualCreditCardDebt($creditCardConsume->getAmount(), $payments);
    }

    /**
     * @param CreditCardConsume $creditCardConsume
     * @return float|int|null
     */
    public function getNextCapitalAmount(CreditCardConsume $creditCardConsume)
    {
        return $this->calculations->calculateNextCapitalAmount(
            $this->getActualCreditCardDebt($creditCardConsume),
            $this->getPendingDues($creditCardConsume)
        );
    }

    public function getNextInterestAmount(CreditCardConsume $creditCardConsume)
    {
        return $this->calculations->calculateNextInterestAmount(
            $this->getActualCreditCardDebt($creditCardConsume),
            $creditCardConsume->getInterest()
        );
    }

    /**
     * Retorna lo que va  apagar= Capital + Interes
     * @param CreditCardConsume $creditCardConsume
     * @return float|int|null
     */
    public function getNextPaymentAmount(CreditCardConsume $creditCardConsume)
    {
        return $this->calculations->calculateNextPaymentAmount(
            $this->getNextCapitalAmount($creditCardConsume),
            $this->getNextInterestAmount($creditCardConsume)
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
    public function getPendingDuesToPay(CreditCardConsume $creditCardConsume)
    {
        return $this->calculations->calculatePendingDuesToPay(
            $this->getActualCreditCardDebt($creditCardConsume),
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
    public function getCreditCardDebtsForUsersByOwner(User $owner)
    {
        $creditCardConsume = $this->cardConsumeRepository->getCreditsCardConsumesByOwner($owner);

        $debtsByUser = [];
        /** @var CreditCardConsume $creditDebts */
        foreach ($creditCardConsume as $creditDebts ){
            $debtsByUser[ $creditDebts->getCreditCardUser()->getId() ][] = array(
                'user' => $creditDebts->getCreditCardUser()->getFullName(),
                'debt' => $creditDebts->getId(),
                'capital_payment' => $this->getNextCapitalAmount( $creditDebts ),
                'interest' => $this->getNextInterestAmount( $creditDebts ),
                'total' => $this->getNextPaymentAmount( $creditDebts )
            );
        }

        return $debtsByUser;
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