<?php

namespace App\Factory\Payments;

use App\Entity\CreditCard\CreditCardConsume;
use App\Entity\CreditCard\CreditCardPayments;

class PaymentsFactory
{
    public static function create(
        CreditCardConsume $cardConsume,
        float $amount,
        float $capitalAmount,
        float $realCapitalAmount,
        float $interestAmount,
        float $monthPayed,
        bool $legalDue = true
    ): CreditCardPayments
    {
        $payment = new CreditCardPayments();
        $payment->setCreditConsume($cardConsume);
        $payment->setAmount($amount);
        $payment->setCapitalAmount($capitalAmount);
        $payment->setRealCapitalAmount($realCapitalAmount);
        $payment->setInterestAmount($interestAmount);
        $payment->setMonthPayed($monthPayed);
        $payment->setLegalDue($legalDue);

        return $payment;
    }
}