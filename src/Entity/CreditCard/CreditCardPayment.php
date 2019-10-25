<?php

namespace App\Entity\CreditCard;

use App\Util\TimestampAbleEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CreditCard\CreditCardPaymentRepository")
 */
class CreditCardPayment
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CreditCard\CreditCardConsume", inversedBy="payments")
     * @ORM\JoinColumn(nullable=false)
     */
    private $creditConsume;

    /**
     *
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $due;

    /**
     * @ORM\Column(type="string")
     * */
    private $monthPayed;

    /**
     * @ORM\Column(type="float")
     */
    private $capitalAmount;

    /**
     * @ORM\Column(type="float")
     * */
    private $realCapitalAmount;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $interestAmount;

    /**
     * @ORM\Column(type="float")
     */
    private $amount;

    /**
     * @ORM\Column(type="boolean")
     * */
    private $legalDue = true;

    use TimestampAbleEntity;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCapitalAmount(): ?float
    {
        return $this->capitalAmount;
    }

    public function setCapitalAmount(float $capitalAmount): self
    {
        $this->capitalAmount = $capitalAmount;

        return $this;
    }

    public function getInterestAmount(): ?float
    {
        return $this->interestAmount;
    }

    public function setInterestAmount(?float $interestAmount): self
    {
        $this->interestAmount = $interestAmount;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getCreditConsume(): ?CreditCardConsume
    {
        return $this->creditConsume;
    }

    public function setCreditConsume(?CreditCardConsume $creditConsume): self
    {
        $this->creditConsume = $creditConsume;

        return $this;
    }

    /**
     * @return mixed
     */
    public function isLegalDue()
    {
        return $this->legalDue;
    }


    /**
     * @param mixed $legalDue
     */
    public function setLegalDue($legalDue): void
    {
        $this->legalDue = $legalDue;
    }

    /**
     * @return mixed
     */
    public function getMonthPayed()
    {
        return $this->monthPayed;
    }

    /**
     * @param mixed $monthPayed
     */
    public function setMonthPayed($monthPayed): void
    {
        $this->monthPayed = $monthPayed;
    }

    /**
     * @return mixed
     */
    public function getRealCapitalAmount()
    {
        return $this->realCapitalAmount;
    }

    /**
     * @param mixed $realCapitalAmount
     */
    public function setRealCapitalAmount($realCapitalAmount): void
    {
        $this->realCapitalAmount = $realCapitalAmount;
    }

    /**
     * @return mixed
     */
    public function getDue()
    {
        return $this->due;
    }

    /**
     * @param mixed $due
     */
    public function setDue($due): void
    {
        $this->due = $due;
    }
}
