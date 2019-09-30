<?php


namespace App\Tests\Extractor\CreditCard;


use App\Entity\CreditCard\CreditCard;
use App\Entity\CreditCard\CreditCardConsume;
use App\Entity\CreditCard\CreditCardUser;
use App\Extractor\CreditCard\CreditCardConsumeExtractor;
use App\Repository\CreditCard\CreditCardPaymentsRepository;
use App\Service\CreditCard\CreditCalculator;
use App\Service\CreditCard\CreditCardConsumeProvider;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class CreditCardConsumeExtractorTest extends TestCase
{
    private $consumeExtractor;

    /**
     * @var MockObject|CreditCalculator
     */
    private $calculator;

    /**
     * @var MockObject|CreditCardPaymentsRepository
     */
    private $paymentsRepository;

    private $creditCardConsume;

    private $cardConsumeProvider;

    private $creditCardConsumeMock;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        $this->cardConsumeProvider = $this->prophesize(CreditCardConsumeProvider::class);
        $this->paymentsRepository = $this->prophesize(CreditCardPaymentsRepository::class);
//        $this->calculator = $this->prophesize(CreditCalculator::class);
        $this->calculator = $this->createPartialMock(CreditCalculator::class, [
            'calculateActualDueToPay',
            'reverseMonth'
        ]);
        $this->creditCardConsumeMock = $this->prophesize(CreditCardConsume::class);
        $this->consumeExtractor = new CreditCardConsumeExtractor(
            $this->cardConsumeProvider->reveal(),
            $this->paymentsRepository->reveal(),
            $this->calculator
        );

        $this->creditCardConsume = $this->creditCardConsumeObject();
    }

    /**
     * @return float
     * @throws Exception
     */
    public function testExtractActualDebt(): float
    {
        $actualDebt = $this->consumeExtractor->extractActualDebt(
            $this->creditCardConsume
        );

        self::assertEquals(800, $actualDebt);

        return $actualDebt;
    }

    /**
     * @return int
     * @throws Exception
     */
    public function testExtractPendingDues(): int
    {
        $pendingDues = $this->consumeExtractor->extractPendingDues(
            $this->creditCardConsume
        );

        self::assertSame(8, $pendingDues);

        return $pendingDues;
    }

    /**
     * @param $actualDebt
     * @param $pendingDues
     * @param $actualDue
     * @param $lastPayedDue
     * @param $expected
     *
     * @dataProvider getNextCapitalAmountProvider
     * @throws Exception
     */
    public function testExtractNextCapitalAmountWhenHasLatePayments($actualDebt, $pendingDues, $actualDue, $lastPayedDue, $expected)
    {
        $this->creditCardConsume->setAmount($actualDebt);
        $this->creditCardConsume->setAmountPayed(0);
        $this->creditCardConsume->setDues($pendingDues + $lastPayedDue);
        $this->creditCardConsume->setDuesPayed($lastPayedDue);

        $this->calculator->expects(self::any())->method('calculateActualDueToPay')->willReturn($actualDue);
        $this->paymentsRepository->getMonthListByConsume(Argument::type(CreditCardConsume::class))->willReturn([]);

        $nextCapitalAmount = $this->consumeExtractor->extractNextCapitalAmount(
            $this->creditCardConsume
        );

        self::assertSame((float)$expected, $nextCapitalAmount);
    }

    public function getNextCapitalAmountProvider()
    {
        return [
            [800, 8, 5, 2, 300],
            [1000, 4, 7, 5, 500],
            [5500, 10, 12, 5, 3850],
            [5500, 5, 1, 1, 0],
        ];
    }

    /**
     * @param $actualDebt
     * @param $pendingDues
     * @param $actualDue
     * @param $lastPayedDue
     * @param $expected
     *
     * @dataProvider getNextCapitalAmountProvider2
     * @throws Exception
     */
    public function testExtractNextCapitalAmountWhenDontHasLatePayments($actualDebt, $pendingDues, $actualDue, $lastPayedDue, $expected)
    {
        $this->creditCardConsume->setAmount($actualDebt);
        $this->creditCardConsume->setAmountPayed(0);
        $this->creditCardConsume->setDues($pendingDues + $lastPayedDue);
        $this->creditCardConsume->setDuesPayed($lastPayedDue);
        $this->calculator->expects(self::any())->method('calculateActualDueToPay')->willReturn($actualDue);
        $this->paymentsRepository->getMonthListByConsume(Argument::type(CreditCardConsume::class))->shouldNotBeCalled();

        $nextCapitalAmount = $this->consumeExtractor->extractNextCapitalAmount(
            $this->creditCardConsume
        );

        self::assertSame((float)$expected, $nextCapitalAmount);
    }

    public function getNextCapitalAmountProvider2()
    {
        return [
            [254, 5, 0, 0, 0],
            [200, 1, 1, 0, 200],
        ];
    }

    /**
     * @dataProvider getInterestAmountProvider
     * @param float $amount
     * @param float $interest
     * @param int $dues
     * @param int $duesPayed
     * @param int $actualDue
     * @param float $expected
     * @param string $message
     * @throws Exception
     */
    public function testExtractNextInterestAmountBasedOnLatePayments(
        float $amount,
        float $interest,
        int $dues,
        int $duesPayed,
        int $actualDue,
        float $expected,
        string $message = ''
    )
    {
        $this->creditCardConsume->setAmount($amount);
        $this->creditCardConsume->setAmountPayed(0);
        $this->creditCardConsume->setInterest($interest);
        $this->creditCardConsume->setDues($dues);
        $this->creditCardConsume->setDuesPayed($duesPayed);

        $this->calculator->expects(self::any())->method('calculateActualDueToPay')->willReturn($actualDue);

        $interestAmount = $this->consumeExtractor->extractNextInterestAmount(
            $this->creditCardConsume
        );

        self::assertEquals((float)$expected, $interestAmount, $message);
    }

    public function getInterestAmountProvider()
    {
        return [
            [1000, 2, 10, 0, 3, 54, '3 pending dues'],
            [2000, 1.8, 10, 1, 10, 180, '9 pending dues'],
            [157400, 2.25, 8, 5, 7, 5902.5, '2 pending dues'],
            [452450, 1.52, 20, 8, 8, 0, 'Not pending dues'],
            [1000, 10, 10, 1, 1, 0, 'Not pending dues'],
            [0, 1.5, 10, 6, 8, 0, 'Without debt'],
            [456100, 0, 10, 1, 8, 0, 'Interest in zero'],
        ];
    }

    /**
     *
     *
     * @throws Exception
     */
    public function testExtractNextPaymentAmount()
    {
        $this->calculator->expects(self::any())->method('calculateActualDueToPay')->willReturn(3);

        $paymentAmount = $this->consumeExtractor->extractNextPaymentAmount(
            $this->creditCardConsume
        );

        self::assertEquals(120, $paymentAmount);
    }

    /**
     *
     * @throws Exception
     */
    public function testExtractPendingPaymentsByConsume()
    {
        $this->creditCardConsume->setAmount(2000);
        $this->creditCardConsume->setAmountPayed(1800);
        $this->creditCardConsume->setDues(10);
        $this->creditCardConsume->setDuesPayed(9);
        $this->creditCardConsume->setInterest(2);
        $firstMonth = new \DateTime();
        $firstMonth->modify('-1 Month');
        $monthFirstPay = $firstMonth->format('Y-m');
        $this->creditCardConsume->setMonthFirstPay($monthFirstPay);

        $firstMonth->modify('-1 Month');
        $this->calculator
            ->method('reverseMonth')
            ->with($monthFirstPay)
            ->willReturn($firstMonth->format('Y-m'))
        ;

        $pendingPayments = $this->consumeExtractor->extractPendingPaymentsByConsume(
            $this->creditCardConsume
        );

        $firstMonth->modify('+1 Month');
        $expected = [
            'number_due' => 10,
            'capital_amount' => (float)200,
            'interest' => (float)4,
            'total_to_pay' => (float)204,
            'payment_month' => $firstMonth->format('Y-m'),
        ];

        self::assertSame([$expected], $pendingPayments);
    }

    /**
     *
     * @dataProvider getActualDueProvider
     * @param int $dues
     * @param int $duesPayed
     * @param int $expected
     */
    public function testGetActualDueToPay(int $dues, int $duesPayed, int $expected)
    {
        $this->creditCardConsume->setDues($dues);
        $this->creditCardConsume->setDuesPayed($duesPayed);

        $actualDueToPay = $this->consumeExtractor->getActualDueToPay(
            $this->creditCardConsume
        );

        self::assertSame($expected, $actualDueToPay);
    }

    public function getActualDueProvider()
    {
        return [
            [10, 8, 9],
            [1, 0, 1],
//            [0, 0, 0], Todo: que tanto sentido tiene esto???
        ];
    }

    /**
     * @throws Exception
     */
    public function testExtractTotalToPayByCreditCardWithOutConsumes()
    {
        $creditCard = new CreditCard();

        $this->getByCreditCardReturn();

        $totalToPay = $this->consumeExtractor->extractTotalToPayByCreditCard($creditCard);

        self::assertEquals(0, $totalToPay);
    }

    /**
     * @throws Exception
     */
    public function testExtractTotalToPayByCreditCard()
    {
        $consume2 = clone $this->creditCardConsume;
        $consume2->setAmount(5000);
        $consume2->setAmountPayed(1000);
        $consume2->setInterest(2);
        $consume2->setDues(20);
        $consume2->setDuesPayed(10);

        $return = [
            $this->creditCardConsume,
            $consume2,
        ];
        $this->getByCreditCardReturn($return);

        $this->calculator
            ->expects(self::exactly(4))
            ->method('calculateActualDueToPay')
            ->withConsecutive(
                [$this->creditCardConsume->getDuesPayed()],
                [$this->creditCardConsume->getDuesPayed()],
                [$consume2->getDuesPayed()],
                [$consume2->getDuesPayed()]
            )
            ->willReturnOnConsecutiveCalls(3, 3, 11, 11);

        $totalToPay = $this->consumeExtractor->extractTotalToPayByCreditCard(new CreditCard());

        self::assertEquals(600, $totalToPay);
    }



    /**
     * @throws Exception
     */
    public function testExtractTotalToPayByCardUser()
    {
        $consume2 = clone $this->creditCardConsume;
        $consume2->setAmount(450000);
        $consume2->setAmountPayed(150000);
        $consume2->setInterest(2.2);
        $consume2->setDues(10);
        $consume2->setDuesPayed(0);

        $return = [
            $this->creditCardConsume,
            $consume2,
        ];
        $this->cardConsumeProvider
            ->getByCardUser(
                Argument::type(CreditCardUser::class),
                null,
                null
            )
            ->shouldBeCalled()
            ->willReturn($return);

        $this->calculator
            ->expects(self::exactly(4))
            ->method('calculateActualDueToPay')
            ->withConsecutive(
                [$this->creditCardConsume->getDuesPayed()],
                [$this->creditCardConsume->getDuesPayed()],
                [$consume2->getDuesPayed()],
                [$consume2->getDuesPayed()]
            )
            ->willReturnOnConsecutiveCalls(3, 3, 1, 1);

        $totalToPay = $this->consumeExtractor->extractTotalToPayByCardUser(new CreditCardUser());

        self::assertEquals(36720, $totalToPay);
    }

    /**
     * @return CreditCardConsume
     * @throws Exception
     */
    private function creditCardConsumeObject(): CreditCardConsume
    {
        $creditCardConsume = new CreditCardConsume();
        $creditCardConsume->setAmount(2000);
        $creditCardConsume->setAmountPayed(1200);
        $creditCardConsume->setDues(10);
        $creditCardConsume->setDuesPayed(2);
        $creditCardConsume->setInterest(2.5);
        $creditCardConsume->setMonthFirstPay('2019-05');
        return $creditCardConsume;
    }

    private function getByCreditCardReturn(array $return = []): void
    {
        $this->cardConsumeProvider
            ->getByCreditCard(
                Argument::type(CreditCard::class),
                null
            )
            ->shouldBeCalled()
            ->willReturn($return);
    }


}