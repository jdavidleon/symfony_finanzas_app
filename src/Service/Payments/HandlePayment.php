<?php


namespace App\Service\Payments;


use App\Entity\CreditCard\CreditCardConsume;
use App\Entity\CreditCard\CreditCardPayments;
use App\Extractor\CreditCard\CreditCardConsumeExtractor;
use App\Factory\Payments\PaymentsFactory;
use App\Repository\CreditCard\CreditCardPaymentsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

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
    /**
     * @var CreditCardPaymentsRepository
     */
    private $paymentsRepository;

    public function __construct(
        CreditCardConsumeExtractor $consumeExtractor,
        EntityManagerInterface $entityManager
    )
    {
        $this->consumeExtractor = $consumeExtractor;
        $this->entityManager = $entityManager;
    }

    /* TODO: Se debe agregar un filtro que determine por cada cuota que se debe*/
    /**
     * @param CreditCardConsume $consume
     * @param $payedValue
     */
    public function processPayment(CreditCardConsume $consume, float $payedValue): void
    {

    }

    /**
     * @param CreditCardConsume $consume
     * @param float $payedValue
     * @param float $capitalAmount
     * @param float $realCapitalAmount
     * @param float $interestAmount
     * @param bool|null $monthPayed
     * @param bool $legalDue
     * @return CreditCardPayments
     * @throws Exception
     */
    private function createCardConsumePayment(
        CreditCardConsume $consume,
        float $payedValue,
        float $capitalAmount,
        float $realCapitalAmount,
        float $interestAmount,
        ?bool $monthPayed,
        bool $legalDue = true
    ): CreditCardPayments
    {
        return PaymentsFactory::create(
            $consume,
            $payedValue,
            $capitalAmount,
            $realCapitalAmount,
            $interestAmount,
            $monthPayed ?? $this->consumeExtractor->extractNextPaymentMonth(),
            $legalDue
        );
    }

    /**
     * @param CreditCardConsume $consume
     * @param $payedValue
     * @return CreditCardPayments
     * @throws Exception
     */
    private function createTimelyPayment(
        CreditCardConsume $consume,
        float $payedValue
    ): CreditCardPayments
    {
        $interestAmount = $this->consumeExtractor->extractNextInterestAmount($consume);
        return $this->createCardConsumePayment(
            $consume,
            $payedValue,
            $payedValue - $interestAmount,
            $this->consumeExtractor->extractNextCapitalAmount($consume),
            $interestAmount,
            $this->consumeExtractor->extractNextPaymentMonth()
        );
    }
}