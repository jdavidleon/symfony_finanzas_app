<?php

namespace App\Service\Payments;

use App\Entity\CreditCard\CreditCardConsume;
use App\Entity\CreditCard\CreditCardPayments;

class PaymentsFactory
{
    public static function create(
        CreditCardConsume $cardConsume,
        $amount,
        $capitalAmount,
        $interestAmount,
        $monthPayed,
        $legalDue = true
    )
    {
        $payment = new CreditCardPayments();
        $payment->setCreditConsume($cardConsume);
        $payment->setAmount($amount);
        $payment->setCapitalAmount($capitalAmount);
        $payment->setInterestAmount($interestAmount);
        $payment->setMonthPayed($monthPayed);
        $payment->setLegalDue($legalDue);

        return $payment;
    }
}