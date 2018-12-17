<?php

namespace App\Entity\CreditCard;

use App\Entity\Creditcard\CreditRelation;
use App\Entity\Security\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="")
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Security\User", inversedBy="franchise")
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
     * @ORM\ManyToMany(targetEntity="CreditRelation.php", mappedBy="creditCard")
     */
    private $creditRelations;

    public function __construct()
    {
        $this->creditRelations = new ArrayCollection();
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
     * @return Collection|CreditRelation[]
     */
    public function getCreditRelations(): Collection
    {
        return $this->creditRelations;
    }

    public function addCreditRelation(CreditRelation $creditRelation): self
    {
        if (!$this->creditRelations->contains($creditRelation)) {
            $this->creditRelations[] = $creditRelation;
            $creditRelation->addCreditCard($this);
        }

        return $this;
    }

    public function removeCreditRelation(CreditRelation $creditRelation): self
    {
        if ($this->creditRelations->contains($creditRelation)) {
            $this->creditRelations->removeElement($creditRelation);
            $creditRelation->removeCreditCard($this);
        }

        return $this;
    }
}
