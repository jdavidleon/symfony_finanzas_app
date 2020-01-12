<?php


namespace App\Tests\Service\Payments;


use App\Entity\CreditCard\CreditCard;
use App\Entity\CreditCard\CreditCardConsume;
use App\Entity\CreditCard\CreditCardPayment;
use App\Entity\CreditCard\CreditCardUser;
use App\Exception\ExcedeAmountDebtException;
use App\Exception\MinimalAmountPaymentRequiredException;
use App\Extractor\CreditCard\CreditCardConsumeExtractor;
use App\Factory\Payments\CreditCardPaymentFactory;
use App\Model\Payment\ConsumePaymentResume;
use App\Repository\CreditCard\CreditCardConsumeRepository;
use App\Service\CreditCard\ConsumeResolver;
use App\Service\Payments\PaymentHandler;
use Doctrine\ORM\EntityManager;
use Exception;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

class PaymentHandlerTest extends TestCase
{
    private $paymentHandler;
    private $consumeExtractor;
    private $em;
    private $paymentFactory;
    /**
     * @var ConsumeResolver|ObjectProphecy
     */
    private $consumeResolver;

    protected function setUp()
    {
        $this->consumeExtractor = $this->prophesize(CreditCardConsumeExtractor::class);
        $this->em = $this->prophesize(EntityManager::class);
        $this->paymentFactory = new CreditCardPaymentFactory();
        $this->consumeResolver = $this->prophesize(ConsumeResolver::class);
        $this->paymentHandler = new PaymentHandler(
            $this->consumeExtractor->reveal(),
            $this->consumeResolver->reveal(),
            $this->em->reveal(),
            $this->paymentFactory
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
        $this->consumeResolver->resolveTotalDebtOfConsumesArray([$consume])->willReturn(1020);

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
        $this->consumeResolver->resolveTotalDebtOfConsumesArray([$consume])->willReturn(2000);
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
        $due1 = $this->createConsumePaymentResume(1, 1000, 100, 20, '2019-01');
        $due2 = $this->createConsumePaymentResume(2, 900, 100, 18, '2019-02');
        $due3 = $this->createConsumePaymentResume(3, 800, 100, 14, '2019-03');

        $dues = [
            $due1,
            $due2,
            $due3,
        ];

        $this->consumeExtractor->extractNextPaymentAmount($consume)->willReturn(352);
        $this->consumeResolver->resolveTotalDebtOfConsumesArray([$consume])->willReturn(1000);
        $this->consumeExtractor->extractPendingPaymentsByConsume($consume, true)->willReturn($dues);

        $this->paymentHandler->processPaymentWithSpecificAmount($consume, 352);

        self::assertCount(3, $consume->getPayments());
        self::assertEquals(300, $consume->getAmountPayed());
        self::assertEquals(3, $consume->getDuesPayed());

        $this->assertLegalPaymentConsume($consume, $dues);

        $this->em->persist($consume)->shouldBeCalledTimes(3);
        $this->em->flush()->shouldBeCalled();
    }

    /**
     * @throws Exception
     */
    public function testProcessPaymentWithSpecificAmountWhitPayedValueMajorToActualPaymentAndMinorToActualDebt()
    {
        $consume = $this->consumeObject(2000000, 2.5, 4, '2020-12');
        $due1 = $this->createConsumePaymentResume(1, 2000000, 500000, 50000, '2020-12');
        $due2 = $this->createConsumePaymentResume(2, 1500000, 500000, 37500, '2021-01');

        $dues = [
            $due1,
            $due2,
        ];

        $this->consumeExtractor->extractNextPaymentAmount($consume)->willReturn(1087500);
        $this->consumeResolver->resolveTotalDebtOfConsumesArray([$consume])->willReturn(2000000);
        $this->consumeExtractor->extractPendingPaymentsByConsume($consume, true)->willReturn($dues);

        $this->paymentHandler->processPaymentWithSpecificAmount($consume, 1300000);

        self::assertCount(3, $consume->getPayments());
        self::assertEquals(1212500, $consume->getAmountPayed());
        self::assertEquals(2, $consume->getDuesPayed());

        $this->assertLegalPaymentConsume($consume, $dues);

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
     * @throws Exception
     */
    public function testProcessPaymentWithSpecificAmountWhenPayTotalDebtAmount()
    {
        $consume = $this->consumeObject(50000, 2.5, 5, '2020-01');
        $due1 = $this->createConsumePaymentResume(1, 50000, 10000, 1250, '2020-02');

        $dues = [
            $due1,
        ];

        $this->consumeExtractor->extractNextPaymentAmount($consume)->willReturn(11250);
        $this->consumeResolver->resolveTotalDebtOfConsumesArray([$consume])->willReturn(51250);
        $this->consumeExtractor->extractPendingPaymentsByConsume($consume, true)->willReturn($dues);

        $this->paymentHandler->processPaymentWithSpecificAmount($consume, 51250);

        self::assertCount(2, $consume->getPayments());
        self::assertEquals(50000, $consume->getAmountPayed());
        self::assertEquals(1, $consume->getDuesPayed());
        self::assertEquals(CreditCardConsume::STATUS_PAYED, $consume->getStatus());

        $this->assertLegalPaymentConsume($consume, $dues);

        $this->em->persist($consume)->shouldBeCalledTimes(2);
        $this->em->flush()->shouldBeCalled();
    }

    /**
     * @throws Exception
     */
    public function testProcessAllPaymentsByCardAndUserIfThereAreNotConsumes()
    {
        $consumeRepo = $this->prophesize(CreditCardConsumeRepository::class);
        $this->em->getRepository(CreditCardConsume::class)->willReturn($consumeRepo);

        $creditCard = new CreditCard();
        $user = new CreditCardUser();
        $consumeRepo->getByCardAndUser($creditCard, $user)->willReturn([]);

        $this->em->persist(Argument::any())->shouldNotBeCalled();
        $this->em->flush()->shouldBeCalled();

        $this->paymentHandler->processAllPaymentsByCardAndUser($creditCard, $user);
    }

    /**
     * @throws Exception
     * */
    public function testProcessAllPaymentsByCardAndUserWhenExistConsumesToPay()
    {
        $consumeRepo = $this->prophesize(CreditCardConsumeRepository::class);
        $this->em->getRepository(CreditCardConsume::class)->willReturn($consumeRepo);

        $creditCard = new CreditCard();
        $user = new CreditCardUser();

        $consume1 = $this->consumeObject(45000, 2.3, 8, '2019-10');
        $due1 = $this->createConsumePaymentResume(1, 45000, 5625, 1035, '2019-10');
        $due2 = $this->createConsumePaymentResume(2, 39375, 5625, 906, '2019-11');
        $dues1 = [$due1, $due2];

        $consume2 = $this->consumeObject(780000, 1.9, 5, '2019-09');
        $due3 = $this->createConsumePaymentResume(1, 780000, 156000, 14820, '2019-09');
        $due4 = $this->createConsumePaymentResume(2, 624000, 156000, 12856, '2019-10');
        $due5 = $this->createConsumePaymentResume(3, 468000, 156000, 8892, '2019-11');
        $dues2 = [$due3, $due4, $due5];

        $consumes = [$consume1, $consume2];
        $consumeRepo->getByCardAndUser($creditCard, $user)->willReturn($consumes);
        $this->consumeExtractor->extractPendingPaymentsByConsume($consume1, true)->willReturn($dues1);
        $this->consumeExtractor->extractPendingPaymentsByConsume($consume2, true)->willReturn($dues2);

        $this->em->persist($consume1)->shouldBeCalledTimes(1);
        $this->em->persist($consume2)->shouldBeCalledTimes(1);
        $this->em->flush()->shouldBeCalled();

        $this->paymentHandler->processAllPaymentsByCardAndUser($creditCard, $user);

        self::assertEquals(2, $consume1->getDuesPayed());
        self::assertCount(2, $consume1->getPayments());
        self::assertEquals(11250, $consume1->getAmountPayed());
        self::assertCount(3, $consume2->getPayments());
        self::assertEquals(468000, $consume2->getAmountPayed());
        self::assertEquals(3, $consume2->getDuesPayed());

        $this->assertLegalPaymentConsume($consume1, $dues1);
        $this->assertLegalPaymentConsume($consume2, $dues2);
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
     * @param $actualDebt
     * @param $capitalAmount
     * @param $interest
     * @param $monthPayed
     * @return ConsumePaymentResume
     */
    private function createConsumePaymentResume(
        $dueNumber,
        $actualDebt,
        $capitalAmount,
        $interest,
        $monthPayed
    ): ConsumePaymentResume {
        return new ConsumePaymentResume(
            $dueNumber,
            $actualDebt,
            $capitalAmount,
            $interest,
            $monthPayed
        );
    }

    /**
     * @param array $dues
     * @param CreditCardConsume $consume
     */
    private function assertLegalPaymentConsume(CreditCardConsume $consume, array $dues): void
    {
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
    }
}