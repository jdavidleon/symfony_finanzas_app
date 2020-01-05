<?php


namespace App\Model\Payment;

use App\Service\CreditCard\CreditCalculator;
use App\Service\DateHelper;
use DateTimeInterface;
use phpDocumentor\Reflection\Types\This;

class ConsumePaymentResume
{
    const STATUS_EXPIRED = 'expired';
    const STATUS_PENDING = 'pending';
    const STATUS_PAYED = 'payed';

    /**
     * @var int
     */
    private $dueNumber;
    /**
     * @var float
     */
    private $capitalAmount;

    /**
     * @var float
     */
    private $interest;
    /**
     * @var float
     */
    private $totalToPay;
    /**
     * @var string
     */
    private $paymentMonth;
    /**
     * @var int
     */
    private $status;
    /**
     * @var bool
     */
    private $payed;
    /**
     * @var float
     */
    private $actualDebt;
    /**
     * @var string
     */
    private $payedAt;

    /**
     * ConsumePaymentResume constructor.
     * @param int|null $dueNumber
     * @param float $actualDebt
     * @param float $capitalAmount
     * @param float $interest
     * @param string|null $paymentMonth
     * @param bool $payed
     * @param DateTimeInterface|null $payedAt
     */
    public function __construct(
        ?int $dueNumber,
        float $actualDebt,
        float $capitalAmount,
        float $interest,
        ?string $paymentMonth,
        bool $payed = false,
        ?DateTimeInterface $payedAt = null
    ) {
        $this->dueNumber = $dueNumber;
        $this->actualDebt = $actualDebt;
        $this->capitalAmount = $capitalAmount;
        $this->interest = $interest;
        $this->totalToPay = $capitalAmount + $interest;
        $this->paymentMonth = $paymentMonth;
        $this->payed = $payed;
        $this->payedAt = $payedAt;
    }

    /**
     * @return int|null
     */
    public function getDueNumber(): ?int
    {
        return $this->dueNumber;
    }

    /**
     * @return float
     */
    public function getActualDebt(): float
    {
        return $this->actualDebt;
    }

    /**
     * @return float
     */
    public function getCapitalAmount(): float
    {
        return $this->capitalAmount;
    }

    /**
     * @return float
     */
    public function getInterest(): float
    {
        return $this->interest;
    }

    /**
     * @return float
     */
    public function getTotalToPay(): float
    {
        return $this->totalToPay;
    }

    /**
     * @return string|null
     */
    public function getPaymentMonth(): ?string
    {
        return $this->paymentMonth;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getStatus(): string
    {
        if ($this->status) {
            return $this->status;
        }

        if ($this->payed) {
            $this->status = self::STATUS_PAYED;
        }else{
            $nextPaymentDate = CreditCalculator::calculateNextPaymentDate();
            $monthsArray = [$nextPaymentDate, $this->paymentMonth];
            if ($nextPaymentDate == $this->paymentMonth || DateHelper::calculateMajorMonth($monthsArray) == $this->paymentMonth)
            {
                $this->status = self::STATUS_PENDING;
            }else{
                $this->status = self::STATUS_EXPIRED;
            }
        }

        return $this->status;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getPayedAt(): ?DateTimeInterface
    {
        return $this->payedAt;
    }
}