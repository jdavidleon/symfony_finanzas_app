<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Payments
 *
 * @ORM\Table(name="payments", indexes={@ORM\Index(name="IDX_65D29B321EAC6E6C", columns={"credit_consume_id"})})
 * @ORM\Entity
 */
class Payments
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var float
     *
     * @ORM\Column(name="capital_amount", type="float", precision=10, scale=0, nullable=false)
     */
    private $capitalAmount;

    /**
     * @var float|null
     *
     * @ORM\Column(name="interest_amount", type="float", precision=10, scale=0, nullable=true)
     */
    private $interestAmount;

    /**
     * @var float
     *
     * @ORM\Column(name="amount", type="float", precision=10, scale=0, nullable=false)
     */
    private $amount;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="payed_at", type="datetime", nullable=false)
     */
    private $payedAt;

    /**
     * @var bool
     *
     * @ORM\Column(name="legal_due", type="boolean", nullable=false)
     */
    private $legalDue;

    /**
     * @var \CreditCardConsume
     *
     * @ORM\ManyToOne(targetEntity="CreditCardConsume")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="credit_consume_id", referencedColumnName="id")
     * })
     */
    private $creditConsume;

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

    public function getPayedAt(): ?\DateTimeInterface
    {
        return $this->payedAt;
    }

    public function setPayedAt(\DateTimeInterface $payedAt): self
    {
        $this->payedAt = $payedAt;

        return $this;
    }

    public function getLegalDue(): ?bool
    {
        return $this->legalDue;
    }

    public function setLegalDue(bool $legalDue): self
    {
        $this->legalDue = $legalDue;

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


}
