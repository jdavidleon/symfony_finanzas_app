<?php


namespace App\Service\Payments;


use App\Entity\CreditCard\CreditCard;
use App\Entity\CreditCard\CreditCardConsume;
use App\Entity\CreditCard\CreditCardPayment;
use App\Entity\CreditCard\CreditCardUser;
use App\Entity\CreditCard\Model\ConsumePaymentResume;
use App\Exception\ExcedeAmountDebtException;
use App\Exception\MinimalAmountPaymentRequiredException;
use App\Extractor\CreditCard\CreditCardConsumeExtractor;
use App\Factory\Payments\CreditCardPaymentFactory;
use App\Factory\Payments\PaymentInterface;
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
        PaymentInterface $cardPaymentFactory
    ) {
        $this->consumeExtractor = $consumeExtractor;
        $this->entityManager = $entityManager;
        $this->cardPaymentFactory = $cardPaymentFactory;
    }

    /**
     * @param CreditCardConsume $consume
     * @param $payedValue
     * @throws Exception
     */
    public function processPaymentWithSpecificAmount(CreditCardConsume $consume, float $payedValue): void
    {
        if ($this->consumeExtractor->extractNextPaymentAmount($consume) > $payedValue) {
            throw new MinimalAmountPaymentRequiredException();
        }
        if ($this->consumeExtractor->extractActualDebt($consume) < $payedValue) {
            throw new ExcedeAmountDebtException();
        }

        $pendingPayments = $this->consumeExtractor->extractPendingPaymentsByConsume($consume, true);
        foreach ($pendingPayments as $payment)
        {
            if ($payedValue >= $payment->getTotalToPay()) {
                $this->addPendingPaymentsFromArrayPayments($consume, [$payment]);
                $this->entityManager->persist($consume);
                $payedValue -= $payment->getTotalToPay();
            }
        }

        if (0 < $payedValue) {
            $this->addNotLegalConsumePayment($consume, $payedValue);
            $this->entityManager->persist($consume);
        }

        $this->entityManager->flush();
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

            $this->addPendingPaymentsFromArrayPayments($consume, $pendingPayments);
            $this->entityManager->persist($consume);
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

            $this->addPendingPaymentsFromArrayPayments($consume, $pendingPayments);
            $this->entityManager->persist($consume);
        }

        $this->entityManager->flush();
    }

    /**
     * @param CreditCardConsume $cardConsume
     * @param ConsumePaymentResume[] $payments
     * @throws Exception
     */
    private function addPendingPaymentsFromArrayPayments(CreditCardConsume $cardConsume, array $payments): void
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
            $cardConsume->addPayment($payment);
        }
    }

    /**
     * @param CreditCardConsume $consume
     * @param float $amountPayed
     * @throws Exception
     */
    private function addNotLegalConsumePayment(CreditCardConsume $consume, float $amountPayed)
    {
        $pay = $this->createCardConsumePayment(
            $consume,
            $amountPayed,
            $amountPayed,
            0,
            0,
            null,
            false
        );
        $consume->addPayment($pay);
    }

    /**
     * @param CreditCardConsume $consume
     * @param float $payedValue
     * @param float $capitalAmount
     * @param float $realCapitalAmount
     * @param float $interestAmount
     * @param string|null $monthPayed
     * @param bool $legalDue
     * @return CreditCardPayment
     */
    private function createCardConsumePayment(
        CreditCardConsume $consume,
        float $payedValue,
        float $capitalAmount,
        float $realCapitalAmount,
        float $interestAmount,
        ?string $monthPayed,
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
            $monthPayed,
            $legalDue
        );
    }
}