<?php


namespace App\Tests\Extractor\CreditCard;


use App\Entity\CreditCard\CreditCard;
use App\Entity\CreditCard\CreditCardConsume;
use App\Entity\CreditCard\CreditCardPayment;
use App\Entity\CreditCard\CreditCardUser;
use App\Entity\Security\User;
use App\Extractor\CreditCard\CreditCardConsumeExtractor;
use App\Model\CreditCard\CardConsumeResume;
use App\Model\Payment\ConsumePaymentResume;
use App\Repository\CreditCard\CreditCardPaymentRepository;
use App\Service\CreditCard\CreditCalculator;
use App\Service\CreditCard\CreditCardConsumeProvider;
use DateTime;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use ReflectionProperty;

class CreditCardConsumeExtractorTest extends TestCase
{

    /**
     * @var CreditCardConsumeExtractor|MockObject
     */
    private $consumeExtractor;

    /**
     * @var MockObject|CreditCardPaymentRepository
     */
    private $paymentsRepository;

    private $creditCardConsume;

    private $cardConsumeProvider;

    /**
     * @var CreditCardConsume|ObjectProphecy
     */
    private $creditCardConsumeMock;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        $this->cardConsumeProvider = $this->prophesize(CreditCardConsumeProvider::class);
        $this->paymentsRepository = $this->prophesize(CreditCardPaymentRepository::class);
        $this->creditCardConsumeMock = $this->prophesize(CreditCardConsume::class);
        $this->consumeExtractor = $this->getMockBuilder(CreditCardConsumeExtractor::class)
            ->setConstructorArgs([
                $this->cardConsumeProvider->reveal(),
                $this->paymentsRepository->reveal()
            ])
            ->setMethods(['extractActualDueToPay'])
            ->getMock();
        ;

        $this->creditCardConsume = $this->creditCardConsumeObject();
    }

    /**
     * @return float
     * @throws Exception
     */
    public function testExtractActualDebt(): float
    {
        $creditCardConsume = $this->creditCardConsumeObject();
        $creditCardConsume->setAmount(1000);
        $creditCardConsume->addAmountPayed(200);
        $actualDebt = $this->consumeExtractor->extractactualdebt(
            $creditCardConsume
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
     * @param $amount
     * @param $amountPayed
     * @param $pendingDues
     * @param $actualDue
     * @param $lastPayedDue
     * @param $expected
     *
     * @throws Exception
     * @dataProvider getNextCapitalAmountProvider
     */
    public function testExtractNextCapitalAmountWhenHasLatePayments($amount, $amountPayed, $pendingDues, $actualDue, $lastPayedDue, $expected)
    {
        $creditCardConsume = $this->creditCardConsumeObject($lastPayedDue);
        $creditCardConsume->setAmount($amount);
        $creditCardConsume->addAmountPayed($amountPayed);
        $creditCardConsume->setDues($pendingDues + $lastPayedDue);

        $this->consumeExtractor->method('extractActualDueToPay')->willReturn($actualDue);

        $this->paymentsRepository->getMonthListByConsume(Argument::type(CreditCardConsume::class))->willReturn([]);

        $nextCapitalAmount = $this->consumeExtractor->extractNextCapitalAmount(
            $creditCardConsume
        );

        self::assertSame((float)$expected, $nextCapitalAmount);
    }

    public function getNextCapitalAmountProvider()
    {
        return [
            [1000, 200, 8, 5, 2, 300],
            [2000, 1400, 4, 7, 5, 300],
            [10000, 4500, 10, 12, 5, 3850],
            [18000, 12500, 5, 1, 1, 0],
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
        $creditCardConsume = $this->creditCardConsumeObject($lastPayedDue);
        $creditCardConsume->setAmount($actualDebt);
        $creditCardConsume->addAmountPayed(0);
        $creditCardConsume->setDues($pendingDues + $lastPayedDue);

        $this->consumeExtractor->method('extractActualDueToPay')->willReturn($actualDue);

        $this->paymentsRepository->getMonthListByConsume(Argument::type(CreditCardConsume::class))->shouldNotBeCalled();

        $nextCapitalAmount = $this->consumeExtractor->extractNextCapitalAmount(
            $creditCardConsume
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
        $creditCardConsume = $this->creditCardConsumeObject($duesPayed);
        $creditCardConsume->setAmount($amount);
        $creditCardConsume->addAmountPayed(0);
        $creditCardConsume->setInterest($interest);
        $creditCardConsume->setDues($dues);

        $this->consumeExtractor->method('extractActualDueToPay')->willReturn($actualDue);

        $interestAmount = $this->consumeExtractor->extractNextInterestAmount(
            $creditCardConsume
        );

        self::assertEquals((float)$expected, $interestAmount, $message);
    }

    public function getInterestAmountProvider()
    {
        return [
            [1000, 2, 10, 0, 3, 54, '3 pending dues'],
            [2000, 1.8, 10, 1, 10, 180, '9 pending dues'],
            [157400, 2.25, 8, 5, 7, 5903, '2 pending dues'],
            [452450, 1.52, 20, 8, 8, 0, 'Not pending dues'],
            [1000, 10, 10, 1, 1, 0, 'Not pending dues'],
            [0, 1.5, 10, 6, 8, 0, 'Without debt'],
            [456100, 0, 10, 1, 8, 0, 'Interest in zero'],
            [5000, 3, 10, 5, 5, 0, 'Actual pay == last pay'],
        ];
    }

    /**
     *
     * @throws Exception
     */
    public function testExtractNextPaymentAmount()
    {
        $this->consumeExtractor->method('extractActualDueToPay')->willReturn(3);
        $this->creditCardConsume->addAmountPayed(400);
        $paymentAmount = $this->consumeExtractor->extractNextPaymentAmount(
            $this->creditCardConsume
        );

        self::assertEquals(240, $paymentAmount);
    }

    /**
     *
     * @throws Exception
     */
    public function testExtractPendingPaymentsByConsume()
    {
        $creditCardConsume = $this->creditCardConsumeObject(9);

        $creditCardConsume->setAmount(2000);
        $creditCardConsume->addAmountPayed(1800);
        $creditCardConsume->setDues(10);
        $creditCardConsume->setInterest(2);
        $firstMonth = new DateTime();
        $firstMonth->modify('-1 Month');
        $monthFirstPay = $firstMonth->format('Y-m');
        $creditCardConsume->setMonthFirstPay($monthFirstPay);

        $pendingPayments = $this->consumeExtractor->extractPendingPaymentsByConsume(
            $creditCardConsume
        );

        $expected = new ConsumePaymentResume(
            10,
            200,
            200,
            4,
            $firstMonth->format('Y-m')
        );

        self::assertEquals([$expected], $pendingPayments);
    }

    /**
     * @throws Exception
     */
    public function testExtractPendingPaymentsByConsumeSinceFirstDue()
    {
        $creditCardConsume = $this->creditCardConsumeObject(0);
        $creditCardConsume->setAmount(5000);
        $creditCardConsume->setDues(2);
        $creditCardConsume->setInterest(2);
        $firstMonth = new DateTime();
        $firstMonth->modify('-1 Month');
        $monthFirstPay = $firstMonth->format('Y-m');
        $creditCardConsume->setMonthFirstPay($monthFirstPay);

        $createdAt = new \DateTimeImmutable();
        $payment = new CreditCardPayment($creditCardConsume);
        $payment
            ->setCapitalAmount(2500)
            ->setRealCapitalAmount(2500)
            ->setInterestAmount(100)
            ->setTotalAmount(2600)
            ->setDue(1)
            ->setMonthPayed($monthFirstPay)
            ->setLegalDue(true)
            ->setCreatedAt($createdAt);

        $creditCardConsume->addPayment($payment);

        $pendingPayments = $this->consumeExtractor->extractPaymentListResumeByConsume(
            $creditCardConsume
        );

        $pay1 = new ConsumePaymentResume(
            1,
            5000,
            2500,
            100,
            $firstMonth->format('Y-m'),
            true,
            $createdAt
        );

        $secondMonth = $firstMonth->modify('+1 month');

        $pay2 = new ConsumePaymentResume(
            2,
            2500,
            2500,
            50,
            $secondMonth->format('Y-m')
        );

        $expected = [
            $pay1,
            $pay2,
        ];

        self::assertEquals($expected, $pendingPayments);
    }

    /**
     *
     * @dataProvider getActualDueProvider
     * @param int $dues
     * @param int $duesPayed
     * @param int $expected
     * @throws Exception
     */
    public function testGetActualDueToPay(int $dues, int $duesPayed, int $expected)
    {
        $creditCardConsume = $this->creditCardConsumeObject($duesPayed);
        $creditCardConsume->setDues($dues);

        $actualDueToPay = $this->consumeExtractor->getActualDueToPay(
            $creditCardConsume
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
        $this->creditCardConsume->addAmountPayed(400);
        $consume2 = $this->creditCardConsumeObject(10);
        $consume2->setAmount(5000);
        $consume2->addAmountPayed(1000);
        $consume2->setInterest(2);
        $consume2->setDues(20);

        $return = [
            $this->creditCardConsume,
            $consume2,
        ];
        $this->getByCreditCardReturn($return);


        $this->consumeExtractor
            ->expects(self::exactly(4))
            ->method('extractActualDueToPay')
            ->withConsecutive(
                [$this->creditCardConsume],
                [$this->creditCardConsume],
                [$consume2],
                [$consume2]
            )
            ->willReturnOnConsecutiveCalls(3, 3, 11, 11);

        $totalToPay = $this->consumeExtractor->extractTotalToPayByCreditCard(new CreditCard());

        self::assertEquals(720, $totalToPay);
    }

    /**
     * @throws Exception
     */
    public function testExtractTotalToPayByCardUser()
    {
        $this->creditCardConsume->addAmountPayed(400);
        $consume2 = $this->creditCardConsumeObject(0);
        $consume2->setAmount(450000);
        $consume2->addAmountPayed(150000);
        $consume2->setInterest(2.2);
        $consume2->setDues(10);

        $return = [
            $this->creditCardConsume,
            $consume2,
        ];
        $creditCardUser = new CreditCardUser();
        $creditCard = new CreditCard();
        $this->cardConsumeProvider
            ->getActivesByCardUser(
                $creditCardUser,
                $creditCard,
                true
            )
            ->shouldBeCalled()
            ->willReturn($return);

        $this->consumeExtractor
            ->expects(self::exactly(4))
            ->method('extractActualDueToPay')
            ->withConsecutive(
                [$this->creditCardConsume],
                [$this->creditCardConsume],
                [$consume2],
                [$consume2]
            )
            ->willReturnOnConsecutiveCalls(3, 3, 1, 1);

        $totalToPay = $this->consumeExtractor->extractTotalToPayByCardUser($creditCardUser, $creditCard, '2019-08');

        self::assertEquals(36840, $totalToPay);
    }

    /**
     * @throws Exception
     */
    public function testExtractTotalToPayByOwner()
    {
        $creditCardConsume = $this->creditCardConsumeObject(0);
        $creditCardConsume->setAmount(2000);
        $creditCardConsume->setInterest(2);

        $date = new DateTime();
        if ((int)$date->format('d') >= 15){
            $date->modify('first day of next month');
        }

        $return = [
            $creditCardConsume
        ];
        $this->cardConsumeProvider
            ->getByOwner(
                Argument::type(User::class),
                $date->format('Y-m')
            )
            ->shouldBeCalled()
            ->willReturn($return);

        $this->consumeExtractor
            ->expects(self::exactly(2))
            ->method('extractActualDueToPay')
            ->withConsecutive(
                [$creditCardConsume],
                [$creditCardConsume]
            )
            ->willReturnOnConsecutiveCalls( 1, 1);

        $totalToPay = $this->consumeExtractor->extractTotalToPayByOwner(new User());

        self::assertEquals(240, $totalToPay);
    }


    /**
     * @throws Exception
     */
    public function testExtractConsumeResume()
    {
        $cardConsume1 = $this->creditCardConsumeObject(0);
        $cardConsume1->setAmount(1000);
        $cardConsume1->setInterest(2.2);
        $cardConsume1->setDues(10);
        $cardConsume1->setStatus(CreditCardConsume::STATUS_PAYING);
        $date = new DateTime();
        $cardConsume1->setMonthFirstPay($date->format('Y-m'));
        $creditCardUser = new CreditCardUser();
        $creditCardUser->setName('J');
        $creditCardUser->setLastName('D');
        $creditCardUser->setAlias('JD');
        $cardConsume1->setCreditCardUser($creditCardUser);
        $creditCard = new CreditCard();
        $cardConsume1->setCreditCard($creditCard);
        $cardConsume1->setDescription('Test Consume');

        $date->modify('-1 Month');
        $this->consumeExtractor->expects(self::any())->method('extractActualDueToPay')->willReturn(1);

        $this->setIdByReflection($cardConsume1, 12);
        $this->setIdByReflection($creditCardUser, 455);
        $this->setIdByReflection($creditCard, 85);

        $resume = new CardConsumeResume(
            $cardConsume1,
            10,
            100,
            22,
            122,
            1000,
            CreditCalculator::calculatePendingPaymentsResume(
                1000,
                2.2,
                10,
                0,
                10,
                $date->format('Y-m')
            )
        );

        $consumesResume = $this->consumeExtractor->extractConsumeResume([$cardConsume1]);

        self::assertEquals([$resume], $consumesResume);
    }

    /**
     * @throws Exception
     */
    public function testExtractNextPaymentMonthWithNullEntryDate()
    {
        $date = new DateTime();

        if ($date->format('d') >= 15) {
            $date->modify('first day of next month');
        }

        $nextPaymentMonth = $this->consumeExtractor->extractNextPaymentMonth(null);

        self::assertSame($date->format('Y-m'), $nextPaymentMonth);
    }

    /**
     * @throws Exception
     */
    public function testExtractNextPaymentMonthOfConsumeWhenDontHavePayments()
    {
        $this->creditCardConsume->setMonthFirstPay('2019-01');

        $nextPaymentMonth = $this->consumeExtractor->extractNextPaymentMonth($this->creditCardConsume);

        self::assertSame('2019-01', $nextPaymentMonth);
    }

    /**
     * @throws Exception
     */
    public function testExtractNextPaymentMonthOfConsumeWhenHasPayments()
    {
        $this->creditCardConsume->setMonthFirstPay('2019-01');
        $payment = new CreditCardPayment($this->creditCardConsume);
        $payment->setMonthPayed('2019-04');
        $this->creditCardConsume->addPayment($payment);

        $payment2 = new CreditCardPayment($this->creditCardConsume);
        $payment2->setMonthPayed('2019-05');
        $this->creditCardConsume->addPayment($payment2);

        $payment3 = new CreditCardPayment($this->creditCardConsume);
        $payment3->setMonthPayed('2019-06');
        $this->creditCardConsume->addPayment($payment3);

        $nextPaymentMonth = $this->consumeExtractor->extractNextPaymentMonth($this->creditCardConsume);

        self::assertSame('2019-07', $nextPaymentMonth);
    }

    /**
     * @throws Exception
     */
    public function testExtractLastPaymentMonthWhenHasPayments()
    {
        $payment1 = new CreditCardPayment($this->creditCardConsume);
        $payment1->setMonthPayed('2020-11');
        $this->creditCardConsume->addPayment($payment1);
        $payment2 = new CreditCardPayment($this->creditCardConsume);
        $payment2->setMonthPayed('2020-12');
        $this->creditCardConsume->addPayment($payment2);
        $payment3 = new CreditCardPayment($this->creditCardConsume);
        $payment3->setMonthPayed('2021-01');
        $this->creditCardConsume->addPayment($payment3);

        $lastPaymentMonth = $this->consumeExtractor->extractLastPaymentMonth($this->creditCardConsume);

        self::assertSame('2021-01', $lastPaymentMonth);
    }


    /**
     * @throws Exception
     */
    public function testExtractLastPaymentMonthWhenHasPaymentsButAreNotLegal()
    {
        $this->creditCardConsume->setMonthFirstPay('2018-09');
        $payment1 = new CreditCardPayment($this->creditCardConsume);
        $payment1->setLegalDue(false);
        $this->creditCardConsume->addPayment($payment1);
        $payment2 = new CreditCardPayment($this->creditCardConsume);
        $payment2->setLegalDue(false);
        $this->creditCardConsume->addPayment($payment2);

        $this->paymentsRepository
            ->getMonthListByConsume($this->creditCardConsume)
            ->willReturn([])
        ;

        $lastPaymentMonth = $this->consumeExtractor->extractLastPaymentMonth($this->creditCardConsume);

        self::assertSame('2018-08', $lastPaymentMonth);
    }


    /**
     * @throws Exception
     */
    public function testExtractLastPaymentMonthWhenDoesNotHasPayments()
    {
        $this->creditCardConsume->setMonthFirstPay('2018-03');

        $lastPaymentMonth = $this->consumeExtractor->extractLastPaymentMonth($this->creditCardConsume);

        self::assertSame('2018-02', $lastPaymentMonth);
    }

    /**
     * @throws Exception
     */
    public function testExtractActualDueToPayWhenDebtIsUpToDate()
    {
        /**
         * @var CreditCardConsumeExtractor|MockObject $consumeExtractorMock
         */
        $consumeExtractorMock = $this->getMockBuilder(CreditCardConsumeExtractor::class)
            ->setConstructorArgs([
                    $this->cardConsumeProvider->reveal(),
                    $this->paymentsRepository->reveal()
                ]
            )
            ->setMethods(['extractLastPaymentMonth'])
            ->getMock();

        /** @var MockObject|CreditCardConsume $consumeMock */
        $consumeMock = $this->createPartialMock(CreditCardConsume::class, [
            'getDuesPayed'
        ]);
        $consumeMock->setDues(15);

        $consumeMock->method('getDuesPayed')->willReturn(5);

        $date = new \DateTime();
        if ($date->format('d') < 15) {
            $date->modify('-1 month');
        }
        $consumeExtractorMock->method('extractLastPaymentMonth')->willReturn($date->format('Y-m'));

        $due = $consumeExtractorMock->extractActualDueToPay($consumeMock);

        self::assertEquals(6, $due);
    }


    /**
     * @throws Exception
     */
    public function testExtractActualDueToPayWhenLastDueMonthIsBeforeActualMonthToPay()
    {
        /**
         * @var CreditCardConsumeExtractor|MockObject $consumeExtractorMock
         */
        $consumeExtractorMock = $this->getMockBuilder(CreditCardConsumeExtractor::class)
            ->setConstructorArgs([
                    $this->cardConsumeProvider->reveal(),
                    $this->paymentsRepository->reveal()
                ]
            )
            ->setMethods(['extractLastPaymentMonth'])
            ->getMock();

        /** @var MockObject|CreditCardConsume $consumeMock */
        $consumeMock = $this->createPartialMock(CreditCardConsume::class, [
            'getDuesPayed'
        ]);
        $consumeMock->setDues(8);

        $consumeMock->method('getDuesPayed')->willReturn(4);

        $date = new \DateTime();
        $date->modify('-10 month');

        $consumeExtractorMock->method('extractLastPaymentMonth')->willReturn($date->format('Y-m'));

        $due = $consumeExtractorMock->extractActualDueToPay($consumeMock);

        self::assertEquals(8, $due);
    }

    /**
     * @throws Exception
     */
//    public function testExtractListGroupedByUser()
//    {
//        $consume1 = new CreditCardConsume();
//        $consume1->setAmount(1000);
//        $consume1->addAmountPayed(0);
//        $consume1->setInterest(2.2);
//        $consume1->setDues(10);
//        $consume1->setDuesPayed(0);
//        $consume1->setStatus(CreditCardConsume::STATUS_PAYING);
//        $consume2 = new CreditCardConsume();
//        $consume2 = new CreditCardConsume();
//
//        $this->consumeExtractor->extractListGroupedBy([], 'user');
//    }
    
    
    /**
     * @param $object
     * @param $value
     * @throws \ReflectionException
     */
    private function setIdByReflection($object, $value)
    {
        $this->setAttByReflection($object, 'id', $value);
    }

    /**
     * @param $object
     * @param $att
     * @param $value
     * @throws \ReflectionException
     */
    private function setAttByReflection($object, $att, $value)
    {
        $reflector = new ReflectionProperty($object, $att);
        $reflector->setAccessible(true);
        $reflector->setValue($object, $value);
    }

    /**
     * @param int $duesPayed
     * @return CreditCardConsume
     * @throws Exception
     */
    private function creditCardConsumeObject(int $duesPayed = 2): CreditCardConsume
    {
        $creditCardConsume = new CreditCardConsume();
        $creditCardConsume->setAmount(2000);
        $creditCardConsume->setDues(10);

        if (0 < $duesPayed) {
            foreach (range(1, $duesPayed) as $due){
                $creditCardConsume->addDuePayed();
            }
        }

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