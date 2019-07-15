<?php

namespace App\Entity\CreditCard;

use App\Util\TimestampAbleEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CreditCard\PaymentsRepository")
 */
class CreditCardPayments
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
     * @ORM\Column(type="string")
     * */
    private $monthPayed;

    /**
     * @ORM\Column(type="float")
     */
    private $capital_amount;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $interest_amount;

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
        return $this->capital_amount;
    }

    public function setCapitalAmount(float $capital_amount): self
    {
        $this->capital_amount = $capital_amount;

        return $this;
    }

    public function getInterestAmount(): ?float
    {
        return $this->interest_amount;
    }

    public function setInterestAmount(?float $interest_amount): self
    {
        $this->interest_amount = $interest_amount;

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
    public function getLegalDue()
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
}
