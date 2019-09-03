<?php


namespace App\Tests\Extractor\CreditCard;


use App\Entity\CreditCard\CreditCard;
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

    private $creditCardConsumeMock;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        $this->cardConsumeProvider = $this->prophesize(CreditCardConsumeProvider::class);
        $this->paymentsRepository = $this->prophesize(CreditCardPaymentsRepository::class);
        $this->calculations = $this->prophesize(CreditCalculations::class);
        $this->creditCardConsumeMock = $this->prophesize(CreditCardConsume::class);
        $this->consumeExtractor = new CreditCardConsumeExtractor(
            $this->cardConsumeProvider->reveal(),
            $this->paymentsRepository->reveal(),
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
        $this->calculateConsumeActualDebtReturn($this->creditCardConsume);

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
        $this->numberOfPendingDuesReturn($this->creditCardConsume);

        $pendingDues = $this->consumeExtractor->extractPendingDues(
            $this->creditCardConsume
        );

        self::assertSame(8, $pendingDues);

        return $pendingDues;
    }

    /**
     * @return float|int|null
     * @throws Exception
     */
    public function testExtractNextCapitalAmount()
    {
        $this->calculateConsumeActualDebtReturn($this->creditCardConsume);
        $this->numberOfPendingDuesReturn($this->creditCardConsume);
        $this->calculateNextCapitalAmountReturn($this->creditCardConsume);

        $nextCapitalAmount = $this->consumeExtractor->extractNextCapitalAmount(
            $this->creditCardConsume
        );

        self::assertEquals(100, $nextCapitalAmount);

        return $nextCapitalAmount;
    }

    /**
     * @return float|int
     */
    public function testExtractNextInterestAmount()
    {
        $this->calculateConsumeActualDebtReturn($this->creditCardConsume);
        $this->calculateNextInterestAmountReturn($this->creditCardConsume);

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
     * @depends testExtractNextCapitalAmount
     * @depends testExtractNextInterestAmount
     */
    public function testExtractNextPaymentAmount(
        float $capitalAmount,
        float $interestAmount
    )
    {
        $this->calculateConsumeActualDebtReturn($this->creditCardConsume, 2);
        $this->numberOfPendingDuesReturn($this->creditCardConsume);
        $this->calculateNextCapitalAmountReturn($this->creditCardConsume);
        $this->calculateNextInterestAmountReturn($this->creditCardConsume);

        $this->calculateNextPaymentAmountReturn($this->creditCardConsume);

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
        $this->calculateConsumeActualDebtReturn($this->creditCardConsume);
        $this->calculations
            ->reverseMonth($this->creditCardConsume->getMonthFirstPay())
            ->shouldBeCalled()
            ->willReturn('2019-04');
        $this->calculations
            ->calculatePendingPaymentsResume(
                800,
                $this->creditCardConsume->getInterest(),
                $this->creditCardConsume->getDues(),
                $this->creditCardConsume->getDuesPayed(),
                '2019-04'
            )
            ->shouldBeCalled();

        $this->consumeExtractor->extractPendingPaymentsByConsume(
            $this->creditCardConsume
        );
    }

    public function testGetActualDueToPay()
    {
        $this->numberOfPendingDuesReturn($this->creditCardConsume);
        $this->calculations
            ->calculateActualDueToPay(
                $this->creditCardConsume->getDues(),
                8
            )
            ->shouldBeCalled()
        ;

        $this->consumeExtractor->getActualDueToPay(
            $this->creditCardConsume
        );
    }

    public function testExtractTotalToPayByCreditCardWithOutConsumes()
    {
        $creditCard = new CreditCard();

        $this->getByCreditCardReturn();

        $totalToPay = $this->consumeExtractor->extractTotalToPayByCreditCard($creditCard);

        self::assertEquals(0, $totalToPay);
    }

    public function testExtractTotalToPayByCreditCard()
    {
        $creditCard = new CreditCard();

        $consume2 = clone $this->creditCardConsume;
        $consume2->setAmount(5000);
        $consume2->setAmountPayed(1000);
        $consume2->setInterest(2);
        $consume2->setDues(20);
        $consume2->setDuesPayed(10);

        $return = [
            $this->creditCardConsume,
            $consume2
        ];
        $this->getByCreditCardReturn($return);

        /** @var CreditCardConsume $consume */
        foreach ($return as $consume){
            $this->calculateConsumeActualDebtReturn($consume, 2);
            $this->numberOfPendingDuesReturn($consume);
            $this->calculateNextCapitalAmountReturn($consume);
            $this->calculateNextInterestAmountReturn($consume);
            $this->calculateNextPaymentAmountReturn($consume);
        }

        $totalToPay = $this->consumeExtractor->extractTotalToPayByCreditCard($creditCard);

        self::assertEquals(600, $totalToPay);
    }

    private function calculateConsumeActualDebtReturn(CreditCardConsume $consume, $times= 1): void
    {
        $actualDebt = $consume->getAmount() - $consume->getAmountPayed();
        $this->calculations
            ->calculateActualCreditCardConsumeDebt(
                $consume->getAmount(),
                $consume->getAmountPayed()
            )
            ->shouldBeCalled()
            ->shouldBeCalledTimes($times)
            ->willReturn((float)$actualDebt);
    }

    private function numberOfPendingDuesReturn(CreditCardConsume $consume): void
    {
        $pendingDues = $consume->getDues() - $consume->getDuesPayed();
        $this->calculations
            ->calculateNumberOfPendingDues(
                $consume->getDues(),
                $consume->getDuesPayed()
            )
            ->shouldBeCalled()
            ->willReturn($pendingDues);
    }

    /**
     * @param CreditCardConsume $consume
     */
    private function calculateNextCapitalAmountReturn(CreditCardConsume $consume): void
    {
        $actualDebt = $consume->getAmount() - $consume->getAmountPayed();
        $pendingDues = $consume->getDues() - $consume->getDuesPayed();
        $capitalAmount = $actualDebt/$pendingDues;
        $this->calculations
            ->calculateNextCapitalAmount($actualDebt, $pendingDues)
            ->shouldBeCalled()
            ->willReturn((float)$capitalAmount);
    }

    private function calculateNextInterestAmountReturn(CreditCardConsume $cardConsume): void
    {
        $actualDebt = $cardConsume->getAmount() - $cardConsume->getAmountPayed();
        $interest = $cardConsume->getInterest();
        $interestAmount = ($actualDebt * $interest) / 100;
        $this->calculations
            ->calculateNextInterestAmount(
                $actualDebt,
                $interest
            )
            ->shouldBeCalled()
            ->willReturn($interestAmount);
    }

    /**
     * @param CreditCardConsume $consume
     */
    private function calculateNextPaymentAmountReturn(CreditCardConsume $consume): void
    {
        $actualDebt = $consume->getAmount() - $consume->getAmountPayed();
        $pendingDues = $consume->getDues() - $consume->getDuesPayed();
        $interestAmount = ($actualDebt * $consume->getInterest()) / 100;
        $capitalAmount = $actualDebt/$pendingDues;
        $this->calculations
            ->calculateNextPaymentAmount($capitalAmount, $interestAmount)
            ->shouldBeCalled()
            ->willReturn((float)$capitalAmount + $interestAmount);
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