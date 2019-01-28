<?php

namespace App\Entity\Debts;

use App\Entity\Security\User;
use App\Util\TimestampableEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="DebtRepository")
 */
class DebtsTypes
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="boolean")
     */
    private $active;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Security\User")
     */
    private $user;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $applyToAll;

    use TimestampableEntity;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

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

    public function getApplyToAll(): ?bool
    {
        return $this->applyToAll;
    }

    public function setApplyToAll(?bool $applyToAll): self
    {
        $this->applyToAll = $applyToAll;

        return $this;
    }
}
