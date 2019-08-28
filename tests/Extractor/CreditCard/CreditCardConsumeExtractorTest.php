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
        $this->calculations = $this->createMock(CreditCalculations::class);
        $this->consumeExtractor = new CreditCardConsumeExtractor(
            $this->cardConsumeProvider,
            $this->paymentsRepository,
            $this->calculations
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
     * @throws Exception
     *
     * @depends testExtractActualDebt
     * @depends testExtractPendingDues
     */
    public function testExtractNextCapitalAmount(float $actualDebt, int $pendingDues)
    {
        $this->calculateConsumeActualDebtReturn();
        $this->numberOfPendingDuesReturn();

        $this->calculations
            ->expects(self::once())
            ->method('calculateNextCapitalAmount')
            ->with($actualDebt, $pendingDues)
            ->willReturn((float)100);

        $consumeExtractor = $this->consumeExtractor->extractNextCapitalAmount(
            $this->creditCardConsume
        );

        self::assertEquals(100, $consumeExtractor);
    }

    private function calculateConsumeActualDebtReturn(): void
    {
        $this->calculations
            ->expects(self::once())
            ->method('calculateActualCreditCardConsumeDebt')
            ->with($this->creditCardConsume->getAmount(), $this->creditCardConsume->getAmountPayed())
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
        return $creditCardConsume;
    }
}