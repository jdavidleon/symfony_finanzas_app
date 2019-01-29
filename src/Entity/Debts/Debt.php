<?php

namespace App\Entity\Debts;

use App\Entity\Security\User;
use App\Util\TimestampableEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Debts\DebtRepository")
 */
class Debt
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Security\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="float")
     */
    private $value;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Debts\Creditor")
     * @ORM\JoinColumn(nullable=false)
     */
    private $creditor;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $paymentDay;

    use TimestampableEntity;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

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

    public function getCreditor(): ?Creditor
    {
        return $this->creditor;
    }

    public function setCreditor(?Creditor $creditor): self
    {
        $this->creditor = $creditor;

        return $this;
    }

    public function getPaymentDay(): ?\DateTimeInterface
    {
        return $this->paymentDay;
    }

    public function setPaymentDay(\DateTimeInterface $paymentDay): self
    {
        $this->paymentDay = $paymentDay;

        return $this;
    }
}
