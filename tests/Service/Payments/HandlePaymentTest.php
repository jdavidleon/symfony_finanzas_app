<?php


namespace App\Tests\Service\Payments;


use App\Entity\CreditCard\CreditCard;
use App\Entity\CreditCard\CreditCardConsume;
use App\Entity\CreditCard\CreditCardPayment;
use App\Entity\CreditCard\CreditCardUser;
use App\Extractor\CreditCard\CreditCardConsumeExtractor;
use App\Service\Payments\PaymentHandler;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Zend\Code\Reflection\MethodReflection;

class HandlePaymentTest extends TestCase
{
    /**
     * @var PaymentHandler;
     * */
    private $handlePayment;

    /**
     * @var CreditCardConsumeExtractor|MockObject
     * */
    private $consumeExtractor;
    /**
     * @var EntityManager
     * */
    private $entityManager;


    private $cardConsume;

    public function setUp(): void
    {
        $this->cardConsume = $this->getConsume();

        $consumeProvider = $this->prophesize(CreditCardConsumeProvider::class);
        $paymentRepository = $this->prophesize(CreditCardPaymentRepository::class);

        $this->consumeExtractor =  $this->getMockBuilder(CreditCardConsumeExtractor::class)
            ->setMethodsExcept([
                'extractPendingPaymentsByConsume'
            ])
            ->setConstructorArgs([
                $consumeProvider->reveal(),
                $paymentRepository->reveal(),
                new CreditCalculator(),
            ])
            ->getMock()
        ;
        $this->entityManager = $this->prophesize(EntityManagerInterface::class);

        $this->handlePayment = new PaymentHandler(
            $this->consumeExtractor,
            $this->entityManager->reveal()
        );
    }

    /**
     * @throws Exception
     */
    public function testProcessPaymentByCardAndUser()
    {
        $creditCardConsumeRepo = $this->prophesize(CreditCardConsumeRepository::class);
        $this->entityManager->getRepository(CreditCardConsume::class)->willReturn($creditCardConsumeRepo);

        $cardConsume1 = $this->getConsume(5000, 2.5, 3);
        $cardConsume2 = $this->getConsume(3000, 2.1, 2);

        $consumesResponse = [
            $cardConsume1,
//            $cardConsume2,
        ];

        $creditCardConsumeRepo
            ->getByCardAndUser(Argument::type(CreditCard::class), Argument::type(CreditCardUser::class))
            ->willReturn($consumesResponse);

        $this->entityManager->persist(Argument::type(CreditCardPayment::class))->shouldBeCalledTimes(5);
        $this->entityManager->flush()->shouldBeCalled();

        $paymentsFactory = $this->prophesize(CreditCardPaymentFactory::class);
        $paymentsFactory::create(Argument::cetera())->willReturn(Argument::type(CreditCardPayment::class));

        $this->handlePayment->processPaymentByCardAndUser(new CreditCard(), new CreditCardUser());
    }


    /**
     * @throws Exception
     */
    public function testTimelyPayment()
    {
        self::assertInstanceOf(PaymentHandler::class, $this->handlePayment);

        $consume = new CreditCardConsume();
        $consume->setAmount(2000);
        $consume->setDues(10);
        $consume->setInterest(1);
        $consume->setMonthFirstPay('08-2019');


        $this->handlePayment->processPayment($consume, 2500);
    }

    /**
     * @throws Exception
     */
    public function testPaymentWhenConsumeDoesNotHavePendingDues()
    {
        $this->cardConsume->setAmount(1000);
        $this->cardConsume->setDues(10);
        $this->cardConsume->addDuePayed();

        $this->consumeExtractor->extractNextPaymentAmount($this->cardConsume)->shouldBeCalled()->willReturn();
        $this->handlePayment->processPayment($this->cardConsume, 5000);
    }

    private function getConsume(float $amount = 1000, float $interest = 2, int $dues = 10)
    {
        $consume = new CreditCardConsume();

        return $consume;
    }
}