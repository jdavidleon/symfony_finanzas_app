<?php


namespace App\Tests\Service\Payments;


use App\Entity\CreditCard\CreditCardConsume;
use App\Entity\CreditCard\CreditCardPayment;
use App\Entity\CreditCard\Model\ConsumePaymentResume;
use App\Exception\ExcedeAmountDebtException;
use App\Exception\MinimalAmountPaymentRequiredException;
use App\Extractor\CreditCard\CreditCardConsumeExtractor;
use App\Factory\Payments\CreditCardPaymentFactory;
use App\Service\Payments\PaymentHandler;
use Doctrine\ORM\EntityManager;
use Exception;
use PHPUnit\Framework\TestCase;

class PaymentHandlerTest extends TestCase
{
    private $paymentHandler;
    private $consumeExtractor;
    private $em;
    private $paymentFactory;

    protected function setUp()
    {
        $this->consumeExtractor = $this->prophesize(CreditCardConsumeExtractor::class);
        $this->em = $this->prophesize(EntityManager::class);
        $this->paymentFactory = $this->prophesize(CreditCardPaymentFactory::class);
        $this->paymentHandler = new PaymentHandler(
            $this->consumeExtractor->reveal(),
            $this->em->reveal(),
            $this->paymentFactory->reveal()
        );
    }

    /**
     * @throws Exception
     *
     */
    public function testProcessPaymentWithSpecificAmountWithAmountMinorToNextPaymentAmount()
    {
        $consume = $this->consumeObject(1000, 2, 10, '2019-05');
        $this->consumeExtractor->extractNextPaymentAmount($consume)->willReturn(352);

        $this->expectException(MinimalAmountPaymentRequiredException::class);

        $this->paymentHandler->processPaymentWithSpecificAmount($consume, 200);
    }

    /**
     * @throws Exception
     */
    public function testProcessPaymentWithSpecificAmountWhitPayedValueMajorToActualDebt()
    {
        $consume = $this->consumeObject(1000, 2, 10, '2019-05');

        $this->consumeExtractor->extractNextPaymentAmount($consume)->willReturn(120);
        $this->consumeExtractor->extractActualDebt($consume)->willReturn(1000);

        $this->expectException(ExcedeAmountDebtException::class);

        $this->paymentHandler->processPaymentWithSpecificAmount($consume, 5000);
    }
    /**
     * @throws Exception
     */
    public function testProcessPaymentWithSpecificAmountIfThereAreNotPendingPayments()
    {
        $consume = new CreditCardConsume();
        $this->consumeExtractor->extractPendingPaymentsByConsume($consume, true)->willReturn([]);

        $this->consumeExtractor->extractNextPaymentAmount($consume)->willReturn(0);
        $this->consumeExtractor->extractActualDebt($consume)->willReturn(2000);
        $this->paymentHandler->processPaymentWithSpecificAmount($consume, 1000);

        self::assertCount(1, $consume->getPayments());

        /** @var CreditCardPayment $payment */
        $payment = $consume->getPayments()->first();
        self::assertEquals(1000, $payment->getCapitalAmount());
        self::assertEquals(0, $payment->getInterestAmount());
        self::assertEquals(0, $payment->getRealCapitalAmount());
        self::assertFalse($payment->isLegalDue());
        self::assertNull($payment->getMonthPayed());

        $this->em->persist($consume)->shouldBeCalledTimes(1);
        $this->em->flush()->shouldBeCalled();
    }

    /**
     * @throws Exception
     */
    public function testProcessPaymentWithSpecificAmountIfThereArePendingPayments()
    {
        $consume = $this->consumeObject(1000, 2, 10, '2019-01');
        $due1 = $this->createConsumePaymentResume(1,100,20,'2019-01');
        $due2 = $this->createConsumePaymentResume(2,100,18,'2019-02');
        $due3 = $this->createConsumePaymentResume(3,100,14,'2019-03');

        $dues = [
            $due1,
            $due2,
            $due3,
        ];

        $this->consumeExtractor->extractNextPaymentAmount($consume)->willReturn(352);
        $this->consumeExtractor->extractActualDebt($consume)->willReturn(1000);
        $this->consumeExtractor->extractPendingPaymentsByConsume($consume, true)->willReturn($dues);

        $this->paymentHandler->processPaymentWithSpecificAmount($consume, 352);

        self::assertCount(3, $consume->getPayments());
        self::assertEquals(300, $consume->getAmountPayed());
        self::assertEquals(3, $consume->getDuesPayed());

        /** @var ConsumePaymentResume $due */
        foreach ($dues as $key => $due) {
            /** @var CreditCardPayment $payment */
            $payment = $consume->getPayments()->get($key);
            self::assertEquals($due->getCapitalAmount(), $payment->getCapitalAmount());
            self::assertEquals($due->getInterest(), $payment->getInterestAmount());
            self::assertEquals($due->getCapitalAmount(), $payment->getRealCapitalAmount());
            self::assertEquals($due->getDueNumber(), $payment->getDue());
            self::assertTrue($payment->isLegalDue());
            self::assertEquals($due->getPaymentMonth(), $payment->getMonthPayed());
        }

        $this->em->persist($consume)->shouldBeCalledTimes(3);
        $this->em->flush()->shouldBeCalled();
    }

    /**
     * @throws Exception
     */
    public function testProcessPaymentWithSpecificAmountWhitPayedValueMajorToActualPaymentAndMinorToActualDebt()
    {
        $consume = $this->consumeObject(2000000, 2.5, 4, '2020-12');
        $due1 = $this->createConsumePaymentResume(1,500000,50000,'2020-12');
        $due2 = $this->createConsumePaymentResume(2,500000,37500,'2021-01');

        $dues = [
            $due1,
            $due2,
        ];

        $this->consumeExtractor->extractNextPaymentAmount($consume)->willReturn(1087500);
        $this->consumeExtractor->extractActualDebt($consume)->willReturn(2000000);
        $this->consumeExtractor->extractPendingPaymentsByConsume($consume, true)->willReturn($dues);

        $this->paymentHandler->processPaymentWithSpecificAmount($consume, 1300000);

        self::assertCount(3, $consume->getPayments());
        self::assertEquals(1212500, $consume->getAmountPayed());
        self::assertEquals(2, $consume->getDuesPayed());

        /** @var ConsumePaymentResume $due */
        foreach ($dues as $key => $due) {
            /** @var CreditCardPayment $payment */
            $payment = $consume->getPayments()->get($key);
            self::assertEquals($due->getCapitalAmount(), $payment->getCapitalAmount());
            self::assertEquals($due->getInterest(), $payment->getInterestAmount());
            self::assertEquals($due->getCapitalAmount(), $payment->getRealCapitalAmount());
            self::assertEquals($due->getDueNumber(), $payment->getDue());
            self::assertTrue($payment->isLegalDue());
            self::assertEquals($due->getPaymentMonth(), $payment->getMonthPayed());
        }

        $noLegalPayment = $consume->getPayments()->get(2);
        self::assertEquals(212500, $noLegalPayment->getCapitalAmount());
        self::assertEquals(0, $noLegalPayment->getInterestAmount());
        self::assertEquals(0, $noLegalPayment->getRealCapitalAmount());
        self::assertNull($noLegalPayment->getDue());
        self::assertFalse($noLegalPayment->isLegalDue());
        self::assertNull($noLegalPayment->getMonthPayed());

        $this->em->persist($consume)->shouldBeCalledTimes(3);
        $this->em->flush()->shouldBeCalled();
    }

    /**
     * @param $amount
     * @param $interest
     * @param $dues
     * @param $firstMonth
     * @return CreditCardConsume
     * @throws Exception
     */
    private function consumeObject($amount, $interest, $dues, $firstMonth): CreditCardConsume
    {
        $consume = new CreditCardConsume();
        $consume->setAmount($amount)
            ->setInterest($interest)
            ->setDues($dues)
            ->setMonthFirstPay($firstMonth);
        return $consume;
    }

    /**
     * @param $dueNumber
     * @param $capitalAmount
     * @param $interest
     * @param $monthPayed
     * @return ConsumePaymentResume
     */
    private function createConsumePaymentResume(
        $dueNumber,
        $capitalAmount,
        $interest,
        $monthPayed
    ): ConsumePaymentResume {
        return new ConsumePaymentResume(
            $dueNumber,
            $capitalAmount,
            $interest,
            $monthPayed
        );
    }
}