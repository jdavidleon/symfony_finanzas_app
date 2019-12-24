<?php

namespace App\Entity\CreditCard;

use App\Entity\Security\User;
use App\Util\TimestampAbleEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CreditCard\CreditCardRepository")
 */
class CreditCard
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $number;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Security\User", inversedBy="creditCards")
     * @ORM\JoinColumn(nullable=false)
     */
    private $owner;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $franchise;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CreditCard\CreditCardConsume", mappedBy="creditCard")
     * @return CreditCardConsume[]
     */
    private $creditCardConsumes;

    use TimestampAbleEntity;

    public function __construct()
    {
        $this->creditCardConsumes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(?string $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    public function getFranchise(): ?string
    {
        return $this->franchise;
    }

    public function setFranchise(string $franchise): self
    {
        $this->franchise = $franchise;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection|CreditCardConsume[]
     */
    public function getCreditCardConsumes(): Collection
    {
        return $this->creditCardConsumes;
    }

    public function addCreditCardConsume(CreditCardConsume $creditCardConsume): self
    {
        if (!$this->creditCardConsumes->contains($creditCardConsume)) {
            $this->creditCardConsumes[] = $creditCardConsume;
            $creditCardConsume->setCreditCard($this);
        }

        return $this;
    }

    public function removeCreditCardConsume(CreditCardConsume $creditCardConsume): self
    {
        if ($this->creditCardConsumes->contains($creditCardConsume)) {
            $this->creditCardConsumes->removeElement($creditCardConsume);
            // set the owning side to null (unless already changed)
            if ($creditCardConsume->getCreditCard() === $this) {
                $creditCardConsume->setCreditCard(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return sprintf('%s (%s)', $this->franchise, $this->number);
    }
}
