<?php

namespace App\Entity\Creditcard;

use App\Entity\CreditCard\CreditCardConsume;
use App\Entity\Security\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Creditcard\creditRelationRepository")
 */
class creditRelation
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
     * @ORM\ManyToMany(targetEntity="App\Entity\Creditcard\CreditCard", inversedBy="creditRelations")
     */
    private $creditCard;

    public function __construct()
    {
        $this->creditCard = new ArrayCollection();
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
}
