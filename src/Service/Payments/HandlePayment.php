<?php


namespace App\Service\Payments;


use App\Entity\CreditCard\CreditCardConsume;
use App\Extractor\CreditCard\CreditCardConsumeExtractor;
use Doctrine\ORM\EntityManagerInterface;

class HandlePayment
{

    /**
     * @var CreditCardConsumeExtractor
     */
    private $consumeExtractor;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(
        CreditCardConsumeExtractor $consumeExtractor,
        EntityManagerInterface $entityManager
    )
    {
        $this->consumeExtractor = $consumeExtractor;
        $this->entityManager = $entityManager;
    }

    /* TODO: Se debe agregar un filtro que determine por cada cuota que se debe*/
    public function processPayment(CreditCardConsume $consume, $payedValue)
    {
        $payment = $this->createCardConsumePayment($consume, $payedValue);

        $this->entityManager->persist($payment);
        $this->entityManager->flush();
    }

    private function createCardConsumePayment(CreditCardConsume $consume, $payedValue, $monthPayed = null)
    {
        return PaymentsFactory::create(
            $consume,
            $payedValue,
            $this->findCapitalAmount($consume, $payedValue),
            $this->consumeExtractor->extractNextInterestAmount($consume),
            $monthPayed ?? $this->consumeExtractor->extractNextPaymentMonth()
        );
    }

    private function findCapitalAmount(CreditCardConsume $consume, $payedValue)
    {
        return $payedValue - $this->consumeExtractor->extractNextInterestAmount($consume);
    }
}