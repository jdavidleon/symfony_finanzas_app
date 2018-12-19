<?php

namespace App\Entity\CreditCard;

use App\Entity\Balance;
use App\Entity\CreditCard\CreditCardConsume;
use App\Entity\Security\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CreditCard\creditRelationRepository")
 */
class CreditRelation
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Security\User", inversedBy="consume")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\CreditCard\CreditCardConsume", inversedBy="creditRelation", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $consume;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\CreditCard\CreditCard", inversedBy="creditRelations")
     */
    private $creditCard;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CreditCard\Payments", mappedBy="CreditRelation")
     */
    private $payments;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\CreditCard\Balance", mappedBy="CreditRelation", cascade={"persist", "remove"})
     */
    private $balance;

    public function __construct()
    {
        $this->creditCard = new ArrayCollection();
        $this->payments = new ArrayCollection();
    }

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

    public function getConsume(): ?CreditCardConsume
    {
        return $this->consume;
    }

    public function setConsume(CreditCardConsume $consume): self
    {
        $this->consume = $consume;

        return $this;
    }

    /**
     * @return Collection|CreditCard[]
     */
    public function getCreditCard(): Collection
    {
        return $this->creditCard;
    }

    public function addCreditCard(CreditCard $creditCard): self
    {
        if (!$this->creditCard->contains($creditCard)) {
            $this->creditCard[] = $creditCard;
        }

        return $this;
    }

    public function removeCreditCard(CreditCard $creditCard): self
    {
        if ($this->creditCard->contains($creditCard)) {
            $this->creditCard->removeElement($creditCard);
        }

        return $this;
    }

    /**
     * @return Collection|Payments[]
     */
    public function getPayments(): Collection
    {
        return $this->payments;
    }

    public function addPayment(Payments $payment): self
    {
        if (!$this->payments->contains($payment)) {
            $this->payments[] = $payment;
            $payment->setCreditRelation($this);
        }

        return $this;
    }

    public function removePayment(Payments $payment): self
    {
        if ($this->payments->contains($payment)) {
            $this->payments->removeElement($payment);
            // set the owning side to null (unless already changed)
            if ($payment->getCreditRelation() === $this) {
                $payment->setCreditRelation(null);
            }
        }

        return $this;
    }

    public function getBalance(): ?Balance
    {
        return $this->balance;
    }

    public function setBalance(Balance $balance): self
    {
        $this->balance = $balance;

        // set the owning side of the relation if necessary
        if ($this !== $balance->getCreditRelation()) {
            $balance->setCreditRelation($this);
        }

        return $this;
    }
}
