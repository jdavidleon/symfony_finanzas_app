<?php


namespace App\Tests\Service\CreditCard;


use App\Service\CreditCard\CreditCalculations;
use PHPUnit\Framework\TestCase;

class CreditCalculationsTest extends TestCase
{

    /**
     * @var CreditCalculations
     */
    private $calculations;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->calculations = new CreditCalculations();
    }

    public function testActualConsumeDebt()
    {
        $actualDebt = $this->calculations->calculateActualCreditCardConsumeDebt(2000, 450);

        self::assertEquals(1550, $actualDebt);
    }

    public function testActualConsumeDebtIfAmountIsAmountIsLessThanPayed()
    {
        $actualDebt = $this->calculations->calculateActualCreditCardConsumeDebt(400, 420);

        self::assertEquals(0, $actualDebt);
    }

    public function testNextCapitalAmount()
    {
        $capital = $this->calculations->calculateNextCapitalAmount(1500000, 12);

        self::assertSame(125000, $capital);
    }

    public function testNextCapitalWithoutAmountAndPendingDues()
    {
        $capital = $this->calculations->calculateNextCapitalAmount(0, 3);

        self::assertSame(0, $capital);
    }

    public function testNextCapitalWithAmountButNonePendingDue()
    {
        $capital = $this->calculations->calculateNextCapitalAmount(200, 0);
        $capital2 = $this->calculations->calculateNextCapitalAmount(500, -2);

        self::assertSame(200, $capital);
        self::assertSame(500, $capital2);
    }

    public function testNextInterestAmount()
    {
        $interest = $this->calculations->calculateNextInterestAmount(100000, 2.25);

        self::assertEquals(2250, $interest);
    }

    public function testNextInterestAmountWithoutInterest()
    {
        $interest = $this->calculations->calculateNextInterestAmount(1000, 0);

        self::assertEquals(0, $interest);
    }

    public function testNextInterestAmountWithoutAmount()
    {
        $interest = $this->calculations->calculateNextInterestAmount(0, 1.06);

        self::assertEquals(0, $interest);
    }

    public function testNextPaymentAmount()
    {
        $payment = $this->calculations->calculateNextPaymentAmount(2000, 250);
        $payment2 = $this->calculations->calculateNextPaymentAmount(1500, 0);

        self::assertSame(2250, $payment);
        self::assertSame(1500, $payment2);
    }

    public function testCalculatePendingDues()
    {
        $dues = $this->calculations->calculateNumberOfPendingDues(20, 10);

        self::assertSame(10, $dues);
    }

    public function testActualDebtToPay()
    {
        $due = $this->calculations->calculateActualDueToPay(10,
            $this->calculations->calculateNumberOfPendingDues(10, 1));

        self::assertSame(2, $due);
    }

    public function testPendingPaymentsResume()
    {
        $resume = $this->calculations->calculatePendingPaymentsResume(
            3000,
            2.5,
            3,
            1
        );

        $resumeExpected = [
            1 => [
                'capital_amount' => 1000,
                'interest' => 75,
                'total_to_pay' => 1075
            ],
            2 => [
                'capital_amount' => 1000,
                'interest' => 50,
                'total_to_pay' => 1050
            ],
            3 => [
                'capital_amount' => 1000,
                'interest' => 25,
                'total_to_pay' => 1025
            ],
        ];

        self::assertEquals($resumeExpected, $resume);
    }

    public function testPendingLastPaymentsResume()
    {
        $resume = $this->calculations->calculatePendingPaymentsResume(
            2000,
            3,
            1,
            3
        );

        $resumeExpected = [
            3 => [
                'capital_amount' => 2000,
                'interest' => 60.0,
                'total_to_pay' => 2060
            ],
        ];

        self::assertEquals($resumeExpected, $resume);
    }
}