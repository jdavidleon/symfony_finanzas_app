<?php


namespace App\Tests\Service\Payments;


use App\Entity\CreditCard\CreditCardConsume;
use App\Extractor\CreditCard\CreditCardConsumeExtractor;
use App\Service\Payments\PaymentHandler;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use PHPUnit\Framework\TestCase;

class HandlePaymentTest extends TestCase
{
    /**
     * @var PaymentHandler;
     * */
    private $handlePayment;

    /**
     * @var CreditCardConsumeExtractor
     * */
    private $cardConsumeExtractor;
    /**
     * @var EntityManager
     * */
    private $entityManager;


    private $cardConsume;

    public function setUp(): void
    {
        $this->cardConsume = $this->getConsume();
        $this->cardConsumeExtractor = $this->prophesize(CreditCardConsumeExtractor::class);
        $this->entityManager = $this->prophesize(EntityManagerInterface::class);

        $this->handlePayment = new PaymentHandler(
            $this->cardConsumeExtractor->reveal(),
            $this->entityManager->reveal()
        );
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

        $this->cardConsumeExtractor->extractNextPaymentAmount($this->cardConsume)->shouldBeCalled()->willReturn();
        $this->handlePayment->processPayment($this->cardConsume, 5000);
    }

    private function getConsume()
    {
        $consume = new CreditCardConsume();

        return $consume;
    }
}