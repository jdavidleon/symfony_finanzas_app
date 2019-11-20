<?php

namespace App\Factory\Payments;

use App\Entity\CreditCard\CreditCardConsume;
use App\Entity\CreditCard\CreditCardPayment;

class CreditCardPaymentFactory implements PaymentInterface
{
    public function create(
        CreditCardConsume $cardConsume,
        float $amount,
        float $capitalAmount,
        float $realCapitalAmount,
        float $interestAmount,
        float $monthPayed,
        bool $legalDue = true
    ): CreditCardPayment
    {
        $payment = new CreditCardPayment($cardConsume);
        $payment->setTotalAmount($amount);
        $payment->setCapitalAmount($capitalAmount);
        $payment->setRealCapitalAmount($realCapitalAmount);
        $payment->setInterestAmount($interestAmount);
        $payment->setMonthPayed($monthPayed);
        $payment->setLegalDue($legalDue);

        return $payment;
    }
}