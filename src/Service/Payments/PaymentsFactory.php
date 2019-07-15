<?php


namespace App\Service\Payments;


use App\Entity\CreditCard\CreditCardConsume;
use App\Entity\CreditCard\CreditCardPayments;

class PaymentsFactory
{

    private $monthPayed;
    /**
     * @var CreditCardConsume
     */
    private $cardConsume;
    /**
     * @var bool
     */
    private $legalDue;
    private $amount;
    private $capitalAmount;
    private $interestAmount;

    public function __construct(
        CreditCardConsume $cardConsume,
        $amount,
        $capitalAmount,
        $interestAmount,
        $monthPayed,
        $legalDue = true
    )
    {
        $this->cardConsume = $cardConsume;
        $this->amount = $amount;
        $this->capitalAmount = $capitalAmount;
        $this->interestAmount = $interestAmount;
        $this->monthPayed = $monthPayed;
        $this->legalDue = $legalDue;
    }

    public function createPayment()
    {
        $payment = new CreditCardPayments();
        $payment->setCreditConsume($this->cardConsume);
        $payment->setAmount($this->amount);
        $payment->setCapitalAmount($this->capitalAmount);
        $payment->setMonthPayed($this->monthPayed);
        $payment->setLegalDue($this->legalDue);

        return $payment;
    }

}