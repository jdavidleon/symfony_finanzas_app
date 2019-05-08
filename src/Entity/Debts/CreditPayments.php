<?php

namespace App\Entity\Debts;

use App\Util\TimestampAbleEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Payments
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class CreditPayments
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var float
     *
     * @ORM\Column(precision=10, scale=0, nullable=false)
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
     * @ORM\Column(type="float", precision=10, scale=0, nullable=false)
     */
    private $totalAmount;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="payed_at", type="datetime", nullable=false)
     */
    private $payedAt;

    /**
     * @var int
     *
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $legalDue;

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

    public function getTotalAmount(): ?float
    {
        return $this->totalAmount;
    }

    public function setTotalAmount(float $totalAmount): self
    {
        $this->totalAmount = $totalAmount;

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

}
