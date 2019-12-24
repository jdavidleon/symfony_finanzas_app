<?php


namespace App\Model\Payment;

use App\Service\CreditCard\CreditCalculator;
use App\Service\DateHelper;

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
     * ConsumePaymentResume constructor.
     * @param int $dueNumber
     * @param float $capitalAmount
     * @param float $interest
     * @param string $paymentMonth
     * @param bool $payed
     */
    public function __construct(
        int $dueNumber,
        float $capitalAmount,
        float $interest,
        string $paymentMonth,
        bool $payed = false
    ) {
        $this->dueNumber = $dueNumber;
        $this->capitalAmount = $capitalAmount;
        $this->interest = $interest;
        $this->totalToPay = $capitalAmount + $interest;
        $this->paymentMonth = $paymentMonth;
        $this->payed = $payed;
    }

    /**
     * @return int
     */
    public function getDueNumber(): int
    {
        return $this->dueNumber;
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
     * @return string
     */
    public function getPaymentMonth(): string
    {
        return $this->paymentMonth;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getStatus(): string
    {
        if ($this->status){
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
}