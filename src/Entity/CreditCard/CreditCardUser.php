<?php

namespace App\Entity\CreditCard;

use App\Entity\Security\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/*TODO: validar el unique constraint*/
/**
 * @ORM\Table(uniqueConstraints={
 *     @UniqueConstraint(
 *          name="parent_unique",
 *          columns={"parent_id", "alias"}
 *     )
 * })
 * @ORM\Entity(repositoryClass="App\Repository\CreditCard\CreditCardUserRepository")
 * @UniqueEntity( fields={"parent","alias"}, message="Ya existe" )
 */
class CreditCardUser
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Security\User" )
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $alias;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updated_at;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $deleted_at;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Security\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CreditCard\CreditCardConsume", mappedBy="userConsume")
     */
    private $creditCardConsumes;

    /**
     * CreditCardUser constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->created_at = new \DateTime();
        $this->creditCardConsumes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $User): self
    {
        $this->user = $User;

        return $this;
    }

    public function getAlias(): ?string
    {
        return $this->alias;
    }

    public function setAlias(string $alias): self
    {
        $this->alias = $alias;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?\DateTimeInterface $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getDeletedAt(): ?\DateTimeInterface
    {
        return $this->deleted_at;
    }

    public function setDeletedAt(?\DateTimeInterface $deleted_at): self
    {
        $this->deleted_at = $deleted_at;

        return $this;
    }

    public function getParent(): ?User
    {
        return $this->parent;
    }

    public function setParent(?User $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getCreditCardConsumes(): ArrayCollection
    {
        return $this->creditCardConsumes;
    }

    public function addCreditCardConsume(CreditCardConsume $creditCardConsume): self
    {
        if (!$this->creditCardConsumes->contains($creditCardConsume)) {
            $this->creditCardConsumes[] = $creditCardConsume;
            $creditCardConsume->setUser($this);
        }

        return $this;
    }

    public function removeCreditCardConsume(CreditCardConsume $creditCardConsume): self
    {
        if ($this->creditCardConsumes->contains($creditCardConsume)) {
            $this->creditCardConsumes->removeElement($creditCardConsume);
            // set the owning side to null (unless already changed)
            if ($creditCardConsume->getUser() === $this) {
                $creditCardConsume->setUser(null);
            }
        }

        return $this;
    }
}
