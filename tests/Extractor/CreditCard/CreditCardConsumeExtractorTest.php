<?php


namespace App\Tests\Extractor\CreditCard;


use App\Entity\CreditCard\CreditCardConsume;
use App\Extractor\CreditCard\CreditCardConsumeExtractor;
use App\Repository\CreditCard\CreditCardPaymentsRepository;
use App\Service\CreditCard\CreditCalculations;
use App\Service\CreditCard\CreditCardConsumeProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CreditCardConsumeExtractorTest extends TestCase
{
    private $consumeExtractor;

    /**
     * @var MockObject
     */
    private $calculations;

    public function setUp(): void
    {
        $cardConsumeProvider = $this->createMock(CreditCardConsumeProvider::class);
        $paymentsRepository = $this->createMock(CreditCardPaymentsRepository::class);
        $this->calculations = $this->createMock(CreditCalculations::class);
        $this->consumeExtractor = new CreditCardConsumeExtractor(
            $cardConsumeProvider,
            $paymentsRepository,
            $this->calculations
        );
    }

//    public function testExtractActualDebt()
//    {
//        $cardConsume = new CreditCardConsume();
//        $cardConsume->setAmount(2000);
//        $cardConsume->setAmountPayed(1200);
//
//        $this->calculations
//            ->expects(self::once())
//            ->method('calculateActualCreditCardConsumeDebt')
//            ->with(2000, 1200)
//            ->willReturn((float)800);
//
//        $consumeExtractor = $this->consumeExtractor->extractActualDebt(
//            $cardConsume
//        );
//
//        self::assertSame(800, $consumeExtractor);
//    }

    public function testExtractNextCapitalAmount()
    {
        $creditCardConsume = new CreditCardConsume();
        $creditCardConsume->setAmount(5000);
        $creditCardConsume->setAmountPayed(1000);
        $creditCardConsume->setDues(10);
        $creditCardConsume->setDuesPayed(2);

        $this->calculations
            ->expects(self::once())
            ->method('calculateActualCreditCardConsumeDebt')
            ->with(5000, 1000)
            ->willReturn((float)4000);

        $this->calculations
            ->expects(self::once())
            ->method('calculateNumberOfPendingDues')
            ->with(10, 2)
            ->willReturn(8);

        $this->calculations
            ->expects(self::once())
            ->method('calculateNextCapitalAmount')
            ->with((float)4000, 8)
            ->willReturn((float)500);

        $consumeExtractor = $this->consumeExtractor->extractNextCapitalAmount($creditCardConsume);

        self::assertSame((float)500, $consumeExtractor);
    }
}