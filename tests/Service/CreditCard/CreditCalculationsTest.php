<?php


namespace App\Tests\Service\CreditCard;


use App\Service\CreditCard\CreditCalculations;
use PHPUnit\Framework\TestCase;

class CreditCalculationsTest extends TestCase
{
    public function testActualConsumeDebt()
    {
        $calculator = new CreditCalculations();
        $actualDebt = $calculator->calculateActualCreditCardConsumeDebt(2000,450);

        self::assertEquals(1550, $actualDebt);
    }
}