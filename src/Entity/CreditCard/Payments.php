<?php

namespace App\Entity\CreditCard;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CreditCard\PaymentsRepository")
 */
class Payments
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CreditCard\CreditRelation", inversedBy="payments")
     * @ORM\JoinColumn(nullable=false)
     */
    private $CreditRelation;

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
     * @ORM\Column(type="datetime")
     */
    private $payed_at;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreditRelation(): ?CreditRelation
    {
        return $this->CreditRelation;
    }

    public function setCreditRelation(?CreditRelation $CreditRelation): self
    {
        $this->CreditRelation = $CreditRelation;

        return $this;
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

    public function getPayedAt(): ?\DateTimeInterface
    {
        return $this->payed_at;
    }

    public function setPayedAt(\DateTimeInterface $payed_at): self
    {
        $this->payed_at = $payed_at;

        return $this;
    }
}
