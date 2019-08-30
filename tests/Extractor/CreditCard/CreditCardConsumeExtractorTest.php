<?php


namespace App\Tests\Extractor\CreditCard;


use App\Entity\CreditCard\CreditCardConsume;
use App\Extractor\CreditCard\CreditCardConsumeExtractor;
use App\Repository\CreditCard\CreditCardPaymentsRepository;
use App\Service\CreditCard\CreditCalculations;
use App\Service\CreditCard\CreditCardConsumeProvider;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class CreditCardConsumeExtractorTest extends TestCase
{
    private $consumeExtractor;

    /**
     * @var MockObject|CreditCalculations
     */
    private $calculations;

    /**
     * @var MockObject|CreditCardPaymentsRepository
     */
    private $paymentsRepository;

    private $creditCardConsume;

    /**
     * @var MockObject
     * */
    private $cardConsumeProvider;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        $this->cardConsumeProvider = $this->createMock(CreditCardConsumeProvider::class);
        $this->paymentsRepository = $this->createMock(CreditCardPaymentsRepository::class);
        $this->calculations = $this->prophesize(CreditCalculations::class);
        $this->consumeExtractor = new CreditCardConsumeExtractor(
            $this->cardConsumeProvider,
            $this->paymentsRepository,
            $this->calculations->reveal()
        );

        $this->creditCardConsume = $this->creditCardConsumeObject();
    }

    /**
     * @return float
     * @throws Exception
     */
    public function testExtractActualDebt(): float
    {
        $this->calculateConsumeActualDebtReturn();

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
        $this->numberOfPendingDuesReturn();

        $pendingDues = $this->consumeExtractor->extractPendingDues(
            $this->creditCardConsume
        );

        self::assertSame(8, $pendingDues);

        return $pendingDues;
    }

    /**
     * @param float $actualDebt
     * @param int $pendingDues
     * @return float|int|null
     * @throws Exception
     *
     * @depends testExtractActualDebt
     * @depends testExtractPendingDues
     */
    public function testExtractNextCapitalAmount(float $actualDebt, int $pendingDues)
    {
        $this->calculateConsumeActualDebtReturn();
        $this->numberOfPendingDuesReturn();
        $this->extractNextCapitalAmountReturn($actualDebt, $pendingDues);

        $nextCapitalAmount = $this->consumeExtractor->extractNextCapitalAmount(
            $this->creditCardConsume
        );

        self::assertEquals(100, $nextCapitalAmount);

        return $nextCapitalAmount;
    }

    /**
     * @param float $actualDebt
     * @return float|int
     *
     * @depends testExtractActualDebt
     */
    public function testExtractNextInterestAmount(float $actualDebt)
    {
        $this->calculateConsumeActualDebtReturn();
        $this->extractNextInterestAmountReturn($actualDebt);

        $interestAmount = $this->consumeExtractor->extractNextInterestAmount(
            $this->creditCardConsume
        );

        self::assertEquals(20, $interestAmount);

        return $interestAmount;
    }

    /**
     *
     * @param float $capitalAmount
     * @param float $interestAmount
     *
     * @param float $actualDebt
     * @param int $pendingDues
     * @depends testExtractNextCapitalAmount
     * @depends testExtractNextInterestAmount
     * @depends testExtractActualDebt
     * @depends testExtractPendingDues
     */
    public function testExtractNextPaymentAmount(
        float $capitalAmount,
        float $interestAmount,
        float $actualDebt,
        int $pendingDues
    )
    {
        $this->calculateConsumeActualDebtReturn(2);
        $this->numberOfPendingDuesReturn();
        $this->extractNextCapitalAmountReturn($actualDebt, $pendingDues);
        $this->extractNextInterestAmountReturn($actualDebt);

        $this->calculations
            ->expects(self::once())
            ->method('calculateNextPaymentAmount')
            ->with($capitalAmount, $interestAmount)
            ->willReturn($capitalAmount + $interestAmount);

        $paymentAmount = $this->consumeExtractor->extractNextPaymentAmount(
            $this->creditCardConsume
        );

        self::assertEquals($capitalAmount + $interestAmount, $paymentAmount);
    }

    /**
     *
     * @throws Exception
     */
    public function testExtractPendingPaymentsByConsume()
    {
        $this->calculateConsumeActualDebtReturn();
        $this->calculations
            ->reverseMonth($this->creditCardConsume->getMonthFirstPay())
            ->shouldBeCalled()
            ->willReturn('2019-04');
        $this->calculations
            ->calculatePendingPaymentsResume(
                800,
                2.5,
                10,
                2,
                '20019-04'
            )
            ->shouldBeCalled();


        $this->consumeExtractor->extractPendingPaymentsByConsume(
            $this->creditCardConsume
        );
    }

    private function calculateConsumeActualDebtReturn(int $times = 1): void
    {
        $this->calculations
            ->calculateActualCreditCardConsumeDebt(
                $this->creditCardConsume->getAmount(),
                $this->creditCardConsume->getAmountPayed()
            )
            ->shouldBeCalled()
            ->shouldBeCalledTimes($times)
            ->willReturn((float)800);
    }

    private function numberOfPendingDuesReturn(): void
    {
        $this->calculations
            ->expects(self::once())
            ->method('calculateNumberOfPendingDues')
            ->with($this->creditCardConsume->getDues(), $this->creditCardConsume->getDuesPayed())
            ->willReturn(8);
    }
    /**
     * @param float $actualDebt
     * @param int $pendingDues
     */
    private function extractNextCapitalAmountReturn(float $actualDebt, int $pendingDues): void
    {
        $this->calculations
            ->expects(self::once())
            ->method('calculateNextCapitalAmount')
            ->with($actualDebt, $pendingDues)
            ->willReturn((float)100);
    }

    private function extractNextInterestAmountReturn(float $actualDebt): void
    {
        $this->calculations
            ->expects(self::once())
            ->method('calculateNextInterestAmount')
            ->with($actualDebt, $this->creditCardConsume->getInterest())
            ->willReturn(20);
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

}