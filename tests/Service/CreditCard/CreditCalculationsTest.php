<?php


namespace App\Tests\Service\CreditCard;


use App\Service\CreditCard\CreditCalculations;
use Exception;
use PHPUnit\Framework\TestCase;

class CreditCalculationsTest extends TestCase
{

    /**
     * @var CreditCalculations
     */
    private static $calculations;

    public static function setUpBeforeClass(): void
    {
        self::$calculations = new CreditCalculations();
    }

    /**
     * @param float $debt
     * @param float $payed
     * @param float $expected
     * @param string $message
     *
     * @dataProvider getActualConsumeDebtCases
     */
    public function testActualConsumeDebt(float $debt, float $payed, float $expected, string $message)
    {
        $actualDebt = self::$calculations->calculateActualCreditCardConsumeDebt($debt, $payed);

        self::assertEquals($expected, $actualDebt, $message);
    }

    public function getActualConsumeDebtCases()
    {
        return [
            [2000, 450, 1550, 'debt < payed'],
            [400, 420, 0, 'payed > debt'],
            [100, 100, 0, 'payed == debt'],
            [0, 100, 0, 'debt = 0 &&'],
        ];
    }

    /**
     * @param float $actualDebt
     * @param int $pendingDues
     * @param float $expected
     * @param string $message
     *
     * @dataProvider getNextCapitalAmountCases
     */
    public function testNextCapitalAmount(float $actualDebt, int $pendingDues, float $expected, string $message)
    {
        $capital = self::$calculations->calculateNextCapitalAmount($actualDebt, $pendingDues);

        self::assertSame($expected, $capital, $message);
    }

    public function getNextCapitalAmountCases()
    {
        return [
            [1500000, 12, 125000, 'actualDebt > 0 & dues > 0'],
            [0, 3, 0, 'actualDebt == 0 & dues > 0'],
            [200, 0, 0, 'actualDebt > 0 & dues == 0'],
            [500, -2, 0, 'actualDebt > 0 & dues < 0'],
            [0, 0, 0, 'actualDebt == 0 & dues == 0'],
            [-100, 2, 0, 'actualDebt < 0 & dues > 0'],
        ];
    }

    /**
     * @param float $actualDebt
     * @param float $interest
     * @param float $expected
     * @param string $message
     *
     * @dataProvider getNextInterestAmountCases
     */
    public function testNextInterestAmount(float $actualDebt, float $interest, float $expected, string $message)
    {
        $interest = self::$calculations->calculateNextInterestAmount($actualDebt, $interest);

        self::assertEquals($expected, $interest, $message);
    }

    public function getNextInterestAmountCases()
    {
        return [
          [100000, 2.25, 2250, 'debt > 0 & interest > 0'],
          [1000, 0, 0, 'debt > 0 & interest == 0'],
          [0, 1.06, 0, 'debt == 0 & interest > 0'],
        ];
    }

    public function testNextPaymentAmount()
    {
        $payment = self::$calculations->calculateNextPaymentAmount(2000, 250);
        $payment2 = self::$calculations->calculateNextPaymentAmount(1500, 0);

        self::assertSame(2250, $payment);
        self::assertSame(1500, $payment2);
    }

    public function testCalculatePendingDues()
    {
        $dues = self::$calculations->calculateNumberOfPendingDues(20, 10);

        self::assertSame(10, $dues);
    }

    public function testActualDebtToPay()
    {
        $due = self::$calculations->calculateActualDueToPay(10,
            self::$calculations->calculateNumberOfPendingDues(10, 1));

        self::assertSame(2, $due);
    }

    /**
     * @throws Exception
     */
    public function testPendingPaymentsResume()
    {
        $resume = self::$calculations->calculatePendingPaymentsResume(
            3000,
            2.5,
            8,
            5,
            '2018-12'
        );

        $resumeExpected = [
            [
                'number_due' => 6,
                'capital_amount' => 1000,
                'interest' => 75,
                'total_to_pay' => 1075,
                'payment_month' => '2019-01'
            ],
            [
                'number_due' => 7,
                'capital_amount' => 1000,
                'interest' => 50,
                'total_to_pay' => 1050,
                'payment_month' => '2019-02'
            ],
            [
                'number_due' => 8,
                'capital_amount' => 1000,
                'interest' => 25,
                'total_to_pay' => 1025,
                'payment_month' => '2019-03'
            ],
        ];

        self::assertEquals($resumeExpected, $resume);
    }

    /**
     * @throws Exception
     */
    public function testPendingLastPaymentsResume()
    {
        $resume = self::$calculations->calculatePendingPaymentsResume(
            2000,
            3,
            4,
            3,
            null
        );

        $resumeExpected = [
            [
                'number_due' => 4,
                'capital_amount' => 2000,
                'interest' => 60.0,
                'total_to_pay' => 2060,
                'payment_month' => '2019-09',
            ],
        ];

        self::assertEquals($resumeExpected, $resume);
    }

    /**
     * @throws Exception
     */
    public function testPendingPaymentsResumeWithoutPendingDues()
    {
        $resume = self::$calculations->calculatePendingPaymentsResume(
            3000,
            2.2,
            5,
            5
        );
        $resume2 = self::$calculations->calculatePendingPaymentsResume(
            3000,
            2.2,
            4,
            7
        );

        self::assertEquals([], $resume);
        self::assertEquals([], $resume2);
    }

    /**
     * @param string|null $date
     * @param string $expected
     * @param string $message
     * @throws Exception
     *
     * @dataProvider getNextPaymentMonthCases
     */
    public function testCalculateNextPaymentMonth(?string $date, ?string $expected, string $message)
    {
        $nextPaymentMonth = self::$calculations->calculateNextPaymentDate($date);

        if (null == $date){
            $expected = date('Y-m');
            if (date('j') >= 15){
                $expected = date("Y-m", strtotime(date('Y-m') . "+ 1 Month"));
            }
        }

        self::assertSame($expected, $nextPaymentMonth, $message);
    }

    public function getNextPaymentMonthCases()
    {
        return [
            [null, '', 'Based on today'],
            ['2019-04', '2019-05', 'day < 15'],
            ['2019-10', '2019-11', 'day < 15 other'],
            ['2018-12', '2019-01', 'day > 15'],
            ['2018-06', '2018-07', 'day == 15'],
            ['2019-01', '2019-02', 'month with 31 days'],
            ['2018-12', '2019-01', 'month and next month with 31 days'],
        ];
    }

    /**
     * @param string|null $date
     * @throws Exception
     *
     * @dataProvider getNextPaymentMonthExceptionsCases
     */
    public function testCalculateNextPaymentMonthExceptions(?string $date)
    {
        $this->expectException(Exception::class);
        self::$calculations->calculateNextPaymentDate($date);
    }

    public function getNextPaymentMonthExceptionsCases()
    {
        return [
            ['2018'],
            ['04-04-2019'],
            ['05-2019'],
        ];
    }

    /**
     * @param string|null $date
     * @param string $expected
     * @param string $message
     * @throws Exception
     *
     * @dataProvider getReverseMonthCases
     */
    public function testReverseMonth(?string $date, ?string $expected, string $message)
    {
        $month = self::$calculations->reverseMonth($date);

        self::assertSame($expected, $month, $message);
    }

    public function getReverseMonthCases()
    {
        return [
            ['2019-04', '2019-03', 'reverse month'],
            ['2019-12', '2019-11', 'December'],
            ['2019-01', '2018-12', 'January'],
        ];
    }

    /**
     * @param string|null $date
     * @param string $expected
     * @param string $message
     * @throws Exception
     *
     * @dataProvider getIncreaseMonthCases
     */
    public function testIncreaseMonth(?string $date, ?string $expected, string $message)
    {
        $month = self::$calculations->increaseMonth($date);

        self::assertSame($expected, $month, $message);
    }

    public function getIncreaseMonthCases()
    {
        return [
            ['2019-05', '2019-06', 'increase month'],
            ['2019-12', '2020-01', 'December'],
            ['2019-01', '2019-02', 'January'],
        ];
    }

    /**
     * @param array $dates
     * @param string $expected
     * @param string $message
     *
     * @dataProvider getCalculateMajorMonthCases
     */
    public function testCalculateMajorMonth(array $dates, string $expected, string $message)
    {
        $month = self::$calculations->calculateMajorMonth($dates);

        self::assertSame($expected, $month, $message);
    }

    public function getCalculateMajorMonthCases()
    {
        return [
            [
                ['2019-04', '2020-04', '2019-11'],
                '2020-04',
                'Major Month'
            ],
            [
                ['2019-11', '2019-12', '2020-01'],
                '2020-01',
                'Major Month'
            ],
        ];
    }
}