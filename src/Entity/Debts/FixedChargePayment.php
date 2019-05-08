<?php

namespace App\Entity\Debts;

use App\Util\TimestampAbleEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Debts\FixedChargesBalanceRepository")
 */
class FixedChargePayment
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Debts\FixedCharges", inversedBy="fixedChargesBalances")
     * @ORM\JoinColumn(nullable=false)
     */
    private $fixedCharge;

    /**
     * @ORM\Column(type="float")
     */
    private $value;

    /**
     * @ORM\Column(type="string", length=10, )
     * */
    private $payedMonth;

    use TimestampAbleEntity;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFixedCharge(): ?FixedCharges
    {
        return $this->fixedCharge;
    }

    public function setFixedCharge(?FixedCharges $fixedCharge): self
    {
        $this->fixedCharge = $fixedCharge;

        return $this;
    }

    public function getValue(): ?float
    {
        return $this->value;
    }

    public function setValue(float $value): self
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPayedMonth()
    {
        return $this->payedMonth;
    }

    /**
     * @param mixed $payedMonth
     */
    public function setPayedMonth($payedMonth): void
    {
        $this->payedMonth = $payedMonth;
    }
}
