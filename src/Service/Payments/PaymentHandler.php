<?php


namespace App\Service\Payments;


use App\Entity\CreditCard\CreditCard;
use App\Entity\CreditCard\CreditCardConsume;
use App\Entity\CreditCard\CreditCardPayment;
use App\Entity\CreditCard\CreditCardUser;
use App\Exception\ExcedeAmountDebtException;
use App\Exception\MinimalAmountPaymentRequiredException;
use App\Extractor\CreditCard\CreditCardConsumeExtractor;
use App\Factory\Payments\CreditCardPaymentFactory;
use App\Factory\Payments\PaymentInterface;
use App\Model\Payment\ConsumePaymentResume;
use App\Service\CreditCard\ConsumeResolver;
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
    /**
     * @var ConsumeResolver
     */
    private $consumeResolver;

    /**
     * PaymentHandler constructor.
     * @param CreditCardConsumeExtractor $consumeExtractor
     * @param ConsumeResolver $consumeResolver
     * @param EntityManagerInterface $entityManager
     * @param PaymentInterface $cardPaymentFactory
     */
    public function __construct(
        CreditCardConsumeExtractor $consumeExtractor,
        ConsumeResolver $consumeResolver,
        EntityManagerInterface $entityManager,
        PaymentInterface $cardPaymentFactory
    ) {
        $this->consumeExtractor = $consumeExtractor;
        $this->consumeResolver = $consumeResolver;
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
        if ($this->consumeResolver->resolveTotalDebtOfConsumesArray([$consume]) < $payedValue) {
            throw new ExcedeAmountDebtException();
        }

        foreach ($this->getPendingPayments($consume) as $payment)
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
    public function processAllPaymentsByCreditCardUser(CreditCardUser $user)
    {
        $consumeRepo = $this->entityManager->getRepository(CreditCardConsume::class);
        
        foreach ($consumeRepo->getActivesByCardUser($user) as $consume) {
            $this->addPendingPaymentsFromArrayPayments($consume, $this->getPendingPayments($consume));
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
            $this->addPendingPaymentsFromArrayPayments($consume, $this->getPendingPayments($consume));
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
    private function addNotLegalConsumePayment(CreditCardConsume $consume, float $amountPayed): void
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
        return $this->cardPaymentFactory->create(
            $consume,
            $payedValue,
            $capitalAmount,
            $realCapitalAmount,
            $interestAmount,
            $monthPayed,
            $legalDue
        );
    }

    /**
     * @param CreditCardConsume $consume
     * @param bool $atDate
     * @return ConsumePaymentResume[]|array
     * @throws Exception
     */
    private function getPendingPayments(CreditCardConsume $consume, bool $atDate = true)
    {
        return $this->consumeExtractor->extractPendingPaymentsByConsume($consume, $atDate);
    }
}