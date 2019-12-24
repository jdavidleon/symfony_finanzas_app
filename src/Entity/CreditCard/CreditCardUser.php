<?php

namespace App\Entity\CreditCard;

use App\Entity\Security\User;
use App\Util\TimestampAbleEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * CreditCardUser
 *
 * @ORM\Table(name="credit_card_user", uniqueConstraints={@ORM\UniqueConstraint(name="parent_unique", columns={"parent_id", "alias"})}, indexes={@ORM\Index(name="IDX_A06A22F9727ACA70", columns={"parent_id"}), @ORM\Index(name="IDX_A06A22F9A76ED395", columns={"user_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\CreditCard\CreditCardUserRepository")
 */
class CreditCardUser
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
     * @var string
     *
     * @ORM\Column(name="alias", type="string", length=255, nullable=false)
     */
    private $alias;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Security\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     * })
     */
    private $parent;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Security\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     * @ORM\Column(name="user_id", nullable=true)
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $lastName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @ORM\Column(type="bigint", nullable=true)
     */
    private $phone;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CreditCard\CreditCardConsume", mappedBy="creditCardUser")
     * */
    private $creditCardConsume;

    use TimestampAbleEntity;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getParent(): ?User
    {
        return $this->parent;
    }

    public function setParent(?User $parent): self
    {
        $this->parent = $parent;

        return $this;
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPhone(): ?int
    {
        return $this->phone;
    }

    public function setPhone(?int $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getFullName()
    {
        return $this->getName() . ' ' . $this->getLastName();
    }

    public function __toString()
    {
        return sprintf('%s (%s)', $this->getFullName(), $this->alias);
    }
}
