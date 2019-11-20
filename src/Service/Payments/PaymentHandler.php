<?php


namespace App\Service\Payments;


use App\Entity\CreditCard\CreditCard;
use App\Entity\CreditCard\CreditCardConsume;
use App\Entity\CreditCard\CreditCardPayment;
use App\Entity\CreditCard\CreditCardUser;
use App\Entity\CreditCard\Model\ConsumePaymentResume;
use App\Extractor\CreditCard\CreditCardConsumeExtractor;
use App\Factory\Payments\CreditCardPaymentFactory;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class PaymentHandler
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
     * @var CreditCardPaymentFactory
     */
    private $cardPaymentFactory;

    public function __construct(
        CreditCardConsumeExtractor $consumeExtractor,
        EntityManagerInterface $entityManager,
        CreditCardPaymentFactory $cardPaymentFactory
    ) {
        $this->consumeExtractor = $consumeExtractor;
        $this->entityManager = $entityManager;
        $this->cardPaymentFactory = $cardPaymentFactory;
    }

    /* TODO: Se debe agregar un filtro que determine por cada cuota que se debe*/
    /**
     * @param CreditCardConsume $consume
     * @param $payedValue
     * @throws Exception
     */
    public function processPaymentWithSpecificAmount(CreditCardConsume $consume, float $payedValue): void
    {
        $pendingPayments = $this->consumeExtractor->extractPendingPaymentsByConsume($consume);
        $pendingToPay = $this->consumeExtractor->extractNextPaymentAmount($consume);

        foreach ($pendingPayments as $payment)
        {
            if (0 == $pendingToPay)
            {
                break;
            }

            if ($payedValue >= $payment->getTotalToPay()) {
                $this->createCardConsumePayment(
                    $consume,
                    $payment->getTotalToPay(),
                    $payment->getInterest(),
                    $payment->getCapitalAmount(),
                    $payment->getInterest(),
                    $payment->getPaymentMonth()
                );
                $payedValue -= $payment->getTotalToPay();
            }
        }

        if (0 < $payedValue) {

        }


    }

    /**
     * @param CreditCardUser $user
     * @throws Exception
     */
    public function processAllPaymentsByUser(CreditCardUser $user)
    {
        $consumeRepo = $this->entityManager->getRepository(CreditCardConsume::class);
        
        foreach ($consumeRepo->getActivesByCardUser($user) as $consume) {
            $pendingPayments = $this->consumeExtractor->extractPendingPaymentsByConsume($consume, true);

            $this->persistPendingPaymentsArray($consume, $pendingPayments);
        }
        
        $this->entityManager->flush();
    }

    /**
     * Procesa los pagos mínimos pendientes por Tarjeta de Crédito y Usuario
     *
     * @param CreditCard $creditCard
     * @param CreditCardUser $user
     * @throws Exception
     */
    public function processAllPaymentsByCardAndUser(CreditCard $creditCard, CreditCardUser $user)
    {
        $consumeRepo = $this->entityManager->getRepository(CreditCardConsume::class);

        foreach ($consumeRepo->getByCardAndUser($creditCard, $user) as $consume) {
            $pendingPayments = $this->consumeExtractor->extractPendingPaymentsByConsume($consume, true);

            $this->persistPendingPaymentsArray($consume, $pendingPayments);
        }

        $this->entityManager->flush();
    }

    /**
     * @param CreditCardConsume $cardConsume
     * @param ConsumePaymentResume[] $payments
     * @throws Exception
     */
    private function persistPendingPaymentsArray(CreditCardConsume $cardConsume, array $payments): void 
    {
        foreach ($payments as $payment) {
            $payment = $this->createCardConsumePayment(
                $cardConsume,
                $payment->getTotalToPay(),
                $payment->getCapitalAmount(),
                $payment->getCapitalAmount(),
                $payment->getInterest(),
                $payment->getPaymentMonth(),
                true
            );

            $this->entityManager->persist($payment);
        }
    }
    
    /**
     * @param CreditCardConsume $consume
     * @param float $payedValue
     * @param float $capitalAmount
     * @param float $realCapitalAmount
     * @param float $interestAmount
     * @param bool|null $monthPayed
     * @param bool $legalDue
     * @return CreditCardPayment
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
    ): CreditCardPayment
    {
        $creditCardPayment = new CreditCardPaymentFactory();
        return $creditCardPayment->create(
            $consume,
            $payedValue,
            $capitalAmount,
            $realCapitalAmount,
            $interestAmount,
            $monthPayed ?? $this->consumeExtractor->extractNextPaymentMonth(),
            $legalDue
        );
    }
}