<?php


namespace App\Tests\Service\Payments;


use App\Entity\CreditCard\CreditCardConsume;
use App\Extractor\CreditCard\CreditCardConsumeExtractor;
use App\Service\Payments\HandlePayment;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use PHPUnit\Framework\TestCase;

class HandlePaymentsTest extends TestCase
{
    /**
     * @var HandlePayment;
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

    public function setUp(): void
    {
        $this->cardConsumeExtractor = $this->prophesize(CreditCardConsumeExtractor::class);
        $this->entityManager = $this->prophesize(EntityManagerInterface::class);

        $this->handlePayment = new HandlePayment(
            $this->cardConsumeExtractor->reveal(),
            $this->entityManager->reveal()
        );
    }

    /**
     * @throws Exception
     */
    public function testTimelyPayment()
    {
        self::assertInstanceOf(HandlePayment::class, $this->handlePayment);

        $consume = new CreditCardConsume();
        $consume->setAmount(2000);
        $consume->setDues(10);
        $consume->setInterest(1);
        $consume->setMonthFirstPay('08-2019');

        $this->handlePayment->processPayment($consume, 2500);
    }
}