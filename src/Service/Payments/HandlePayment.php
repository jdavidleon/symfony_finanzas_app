<?php


namespace App\Service\Payments;


use App\Entity\CreditCard\CreditCard;
use App\Entity\CreditCard\CreditCardConsume;
use App\Entity\CreditCard\CreditCardPayment;
use App\Entity\CreditCard\CreditCardUser;
use App\Extractor\CreditCard\CreditCardConsumeExtractor;
use App\Factory\Payments\PaymentsFactory;
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
     * @param CreditCard $creditCard
     * @param CreditCardUser $user
     * @throws Exception
     */
    public function processPaymentByCardAndUser(CreditCard $creditCard, CreditCardUser $user)
    {
        $consumeRepo = $this->entityManager->getRepository(CreditCardConsume::class);

        foreach ($consumeRepo->getByCardAndUser($creditCard, $user) as $consume) {
            $pendingPayments = $this->consumeExtractor->extractPendingPaymentsByConsume($consume);

            foreach ($pendingPayments as $payment) {
                $payment = $this->createCardConsumePayment(
                    $consume,
                    $payment['total_to_pay'],
                    $payment['capital_amount'],
                    $payment['capital_amount'],
                    $payment['interest'],
                    $payment['payment_month'],
                    true
                );

                $this->entityManager->persist($payment);
            }
        }

        $this->entityManager->flush();
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
     * @return CreditCardPayment
     * @throws Exception
     */
    private function createTimelyPayment(
        CreditCardConsume $consume,
        float $payedValue
    ): CreditCardPayment
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