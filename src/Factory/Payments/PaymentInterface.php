<?php


namespace App\Factory\Payments;


use App\Util\DebtInterface;

interface PaymentInterface
{
    public function createPayment(DebtInterface $debt, $amountPayed) :self;
}