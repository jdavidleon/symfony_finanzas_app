<?php


namespace App\Tests\Service\CreditCard;


use App\Model\Payment\ConsumePaymentResume;
use App\Service\CreditCard\CreditCalculator;
use Exception;
use PHPUnit\Framework\TestCase;

class CreditCalculatorTest extends TestCase
{

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
        $actualDebt = CreditCalculator::calculateActualCreditCardConsumeDebt($debt, $payed);

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
    public function testCalculateCapitalAmount(float $actualDebt, int $pendingDues, float $expected, string $message)
    {
        $capital = CreditCalculator::calculateCapitalAmount($actualDebt, $pendingDues);

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
            [1000, 10, 100, 'actualDebt < 0 & dues > 0'],
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
        $interest = CreditCalculator::calculateInterestAmount($actualDebt, $interest);

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
        $payment = CreditCalculator::calculateNextPaymentAmount(2000, 250);
        $payment2 = CreditCalculator::calculateNextPaymentAmount(1500, 0);

        self::assertEquals(2250, $payment);
        self::assertEquals(1500, $payment2);
    }

    public function testCalculatePendingDues()
    {
        $dues = CreditCalculator::calculateNumberOfPendingDues(20, 10);

        self::assertSame(10, $dues);
    }

    /**
     * @param int $totalDues
     * @param int $payedDues
     *
     * @param int $expected
     * @param string $message
     * @dataProvider getActualDueToPayProvider
     */
    public function testGetNextDebtToPay(int $totalDues, int $payedDues, ?int $expected, string $message)
    {
        $due = CreditCalculator::calculateNextDueToPay($totalDues,
            CreditCalculator::calculateNumberOfPendingDues($totalDues, $payedDues));

        self::assertSame($expected, $due, $message);
    }

    public function getActualDueToPayProvider()
    {
        return [
            [10, 1, 2, 'Normal next month'],
            [20, 20, null, 'Without pending dues'],
            [15, 17, null, 'PayedDues > TotalDues'],
        ];
    }

    /**
     * @throws Exception
     */
    public function testPendingPaymentsResume()
    {
        $resume = CreditCalculator::calculatePendingPaymentsResume(
            3000,
            2.5,
            8,
            5,
            8,
            '2018-12'
        );

        $resumeExpected = [
            new ConsumePaymentResume(
                6,
                3000,
                1000,
                75,
                '2019-01'
            ),
            new ConsumePaymentResume(
                7,
                2000,
                1000,
                50,
                '2019-02'
            ),
            new ConsumePaymentResume(
                8,
                1000,
                1000,
                25,
                '2019-03'
            ),
        ];

        self::assertEquals($resumeExpected, $resume);
    }

    /**
     * @throws Exception
     */
    public function testPendingLastPaymentsResume()
    {
        $resume = CreditCalculator::calculatePendingPaymentsResume(
            2000,
            3,
            4,
            3,
            null
        );

        $date = new \DateTime();
        if ((int)$date->format('d') >= 15){
            $date->modify('+1 Month');
        }

        $resumeExpected = [
            new ConsumePaymentResume(
                4,
                2000,
                2000,
                60.0,
                $date->format('Y-m')
            )
        ];

        self::assertEquals($resumeExpected, $resume);
    }

    /**
     * @throws Exception
     */
    public function testPendingPaymentsResumeWithoutPendingDues()
    {
        $resume = CreditCalculator::calculatePendingPaymentsResume(
            3000,
            2.2,
            5,
            5
        );
        $resume2 = CreditCalculator::calculatePendingPaymentsResume(
            3000,
            2.2,
            4,
            7
        );

        self::assertEquals([], $resume);
        self::assertEquals([], $resume2);
    }

    /**
     * @throws Exception
     */
    public function testPendingPaymentsResumeWhenPayedDuesIsGreaterThanOrEqualToEndDue()
    {
        $resume = CreditCalculator::calculatePendingPaymentsResume(
            3000,
            2.2,
            15,
            9,
            6
        );
        $resume2 = CreditCalculator::calculatePendingPaymentsResume(
            3000,
            2.2,
            14,
            7,
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
        $nextPaymentMonth = CreditCalculator::calculateNextPaymentDate($date);

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
        CreditCalculator::calculateNextPaymentDate($date);
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
     * @param $totalDues
     * @param $lastPayedDue
     * @param $lastMonthPayed
     * @param $nextPaymentMonth
     * @param $expected
     *
     * @dataProvider getCalculateActualDueToPayProvider
     */
    public function testCalculateActualDueToPay($totalDues, $lastPayedDue, $lastMonthPayed, $nextPaymentMonth, $expected)
    {
        $actualDueToPay = CreditCalculator::calculateActualDueToPay($totalDues, $lastPayedDue, $lastMonthPayed, $nextPaymentMonth);

        self::assertSame($expected, $actualDueToPay);
    }

    public function getCalculateActualDueToPayProvider()
    {
        return [
            [20 , 2, '2019-04', '2019-09', 7],
            [20 , 0, '2019-11', '2020-03', 4],
            [20 , 7, '2019-05', '2019-06', 8],
            [20 , 1, '2019-05', '2019-05', 1],
            [20 , 0, '2020-01', '2019-12', 0],
            [19 , 2, '2018-01', '2020-12', 19],
            [2 , 0, '2019-06', '2019-07', 1],
            [3 , 0, '2019-04', '2019-07', 3],
            // [12, '2020-01', '2019-12', 11], /*Todo: Tiene sentido este caso???*/
        ];
    }
}