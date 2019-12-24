<?php


namespace App\Model\CreditCard;


use App\Entity\CreditCard\CreditCardConsume;

class CardConsumeResume
{

    /**
     * @var CreditCardConsume
     */
    private $cardConsume;
    /**
     * @var int
     */
    private $pendingDues;
    /**
     * @var float
     */
    private $nextCapitalAmount;
    /**
     * @var float
     */
    private $nextInterestAmount;
    /**
     * @var float
     */
    private $nextPaymentAmount;
    /**
     * @var float
     */
    private $pendingAmount;
    /**
     * @var array
     */
    private $pendingPayments;
    /**
     * @var float
     */
    private $mora;

    public function __construct(
        CreditCardConsume $cardConsume,
        int $pendingDues,
        float $nextCapitalAmount,
        float $nextInterestAmount,
        float $nextPaymentAmount,
        float $pendingAmount,
        array $pendingPayments,
        float $mora = 0
    )
    {
        $this->cardConsume = $cardConsume;
        $this->pendingDues = $pendingDues;
        $this->nextCapitalAmount = $nextCapitalAmount;
        $this->nextInterestAmount = $nextInterestAmount;
        $this->nextPaymentAmount = $nextPaymentAmount;
        $this->pendingAmount = $pendingAmount;
        $this->pendingPayments = $pendingPayments;
        $this->mora = $mora;
    }

    /**
     * @return CreditCardConsume
     */
    public function getCardConsume(): CreditCardConsume
    {
        return $this->cardConsume;
    }

    /**
     * @return int
     */
    public function getPendingDues(): int
    {
        return $this->pendingDues;
    }

    /**
     * @return float
     */
    public function getNextCapitalAmount(): float
    {
        return $this->nextCapitalAmount;
    }

    /**
     * @return float
     */
    public function getNextInterestAmount(): float
    {
        return $this->nextInterestAmount;
    }

    /**
     * @return float
     */
    public function getNextPaymentAmount(): float
    {
        return $this->nextPaymentAmount;
    }

    /**
     * @return float
     */
    public function getPendingAmount(): float
    {
        return $this->pendingAmount;
    }

    /**
     * @return array
     */
    public function getPendingPayments(): array
    {
        return $this->pendingPayments;
    }

    /**
     * @return float
     */
    public function getMora(): float
    {
        return $this->mora;
    }

}