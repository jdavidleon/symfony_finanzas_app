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
        return $creditCardConsume->getDues() - count( $creditCardConsume->getPayments() );
    }

    public function getActualDebt(Payments $payments, $amount)
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
        return $this->getNextCapitalAmount($creditCardConsume) * $creditCardConsume->getInterest();
    }

    public function getNextPaymentAmount(CreditCardConsume $creditCardConsume)
    {
        return $this->getNextCapitalAmount($creditCardConsume) + $this->getNextInterestAmount($creditCardConsume);
    }

    public function getDuesToPay(CreditCardConsume $creditCardConsume)
    {
        $payments = $creditCardConsume->getPayments();

//        dump($payments instanceof Payments); die;
//        dump( $payments-> )
        /** @var Payments $payments */
        $actualDebt = $this->getActualDebt( $payments, $creditCardConsume->getAmount());
        $interest = $creditCardConsume->getInterest();
        $dues = $this->getPendingDues( $creditCardConsume );
        $dueNumber = $creditCardConsume->getDues() - $dues;

        $capitalMonthlyAmount = $actualDebt / $dues;

        for ( $i = $dueNumber; $i <= $dues;  $i++ ){
            $interestToPay = $actualDebt * $interest;
            $this->duesToPay[$dueNumber] = array(
                'capital_amount' => $capitalMonthlyAmount,
                'interest' => $interestToPay,
                'total_to_pay' =>  $capitalMonthlyAmount + $interestToPay
            );
            $actualDebt -= $capitalMonthlyAmount;
        }

        return $this->duesToPay;
    }

}

