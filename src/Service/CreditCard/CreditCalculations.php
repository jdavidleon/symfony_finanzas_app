<?php
/**
 * Created by PhpStorm.
 * User: JLEON
 * Date: 21/12/2018
 * Time: 3:59 PM
 */

namespace App\Service\CreditCard;

use App\Entity\CreditCard\CreditCardConsume;
use App\Entity\CreditCard\Payments;
use App\Repository\CreditCard\CreditCardConsumeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use phpDocumentor\Reflection\Types\Array_;

class CreditCalculations
{

    /**
     * @var CreditCardConsumeRepository
     */
    private $cardConsumeRepository;

    private $duesToPay = array();

    public function __construct(
        CreditCardConsumeRepository $cardConsumeRepository
    )
    {
        $this->cardConsumeRepository = $cardConsumeRepository;
    }

    /**
     * @param CreditCardConsume $creditCardConsume
     * @return float|int|null
     */
    public function getNextCapitalAmount(CreditCardConsume $creditCardConsume)
    {
        $amount = $creditCardConsume->getAmount();
        $payments = $creditCardConsume->getPayments();

        /** @var Payments $payments */
        return $this->getActualDebt($payments, $amount) / $this->getPendingDues($creditCardConsume);
    }

    public function getPendingDues(CreditCardConsume $creditCardConsume)
    {
        return $creditCardConsume->getDues() - count( $this->cardConsumeRepository->getDuesPayments( $creditCardConsume->getUser() ) );
    }

    public function getActualDebt($payments, $amount)
    {
        $payed = 0;
        /* @var Payments $pay */
        foreach ( $payments as $pay ){
            $payed += $pay->getCapitalAmount();
        }

        return $amount - $payed;
    }

    public function getNextInterestAmount(CreditCardConsume $creditCardConsume)
    {
        return ( $this->getActualDebt($creditCardConsume->getPayments(), $creditCardConsume->getAmount()) * $creditCardConsume->getInterest() ) / 100;
    }

    public function getNextPaymentAmount(CreditCardConsume $creditCardConsume)
    {
        return $this->getNextCapitalAmount($creditCardConsume) + $this->getNextInterestAmount($creditCardConsume);
    }

    public function getDuesToPay(CreditCardConsume $creditCardConsume)
    {
        $payments = $creditCardConsume->getPayments();

        /** @var Payments $payments */
        $actualDebt = $this->getActualDebt( $payments, $creditCardConsume->getAmount());
        $interest = $creditCardConsume->getInterest();
        $dues = $this->getPendingDues( $creditCardConsume );
        $dueNumber = $creditCardConsume->getDues() - $dues + 1;
        $capitalMonthlyAmount = $actualDebt / $dues;

        for ( $i = $dueNumber; $i <= $dues;  $i++ ){
            $interestToPay = ( ( $actualDebt * $interest ) / 100 );
            $this->duesToPay[ $i ] = array(
                'capital_amount' => $capitalMonthlyAmount,
                'interest' => $interestToPay,
                'total_to_pay' =>  $capitalMonthlyAmount + $interestToPay
            );
            $actualDebt -= $capitalMonthlyAmount;
        }

        return $this->duesToPay;
    }

    /**
     * @param array $creditCardConsume
     * @return array
     */
    public function getCreditCardDebtsByUser(Array $creditCardConsume)
    {
        $debtsByUser = [];
        /** @var CreditCardConsume $creditDebts */
        foreach ($creditCardConsume as $creditDebts ){
            $user = $creditDebts->getUser()->getName().' '.$creditDebts->getUser()->getLastName();
            $debtsByUser[ $creditDebts->getUser()->getId() ][] = array(
                'user' => $user,
                'Deuda' => $creditDebts->getId(),
                'abono_capital' => $this->getNextCapitalAmount( $creditDebts ),
                'intereses' => $this->getNextInterestAmount( $creditDebts ),
                'total' => $this->getNextPaymentAmount( $creditDebts )
            );
        }

        return $debtsByUser;
    }

    /**
     * @param array $debtsByUser
     * @return array
     */
    public function getDebtsByUserInCreditCard(Array $debtsByUser)
    {
        $userDebt = [];
        foreach ($debtsByUser as $key => $userDebts ){
            $total = $capital = $interest = 0;
            foreach ( $userDebts as $debt ){
                $capital += $debt['abono_capital'];
                $interest += $debt['intereses'];
                $total += $debt['total'];
            }
            $userDebt[$key] = array(
                'usuario' => $userDebts[0]['user'],
                'capital_total' => $capital,
                'interes_total' => $interest,
                'pago_total' => $total
            );
        }

        return $userDebt;
    }

}

