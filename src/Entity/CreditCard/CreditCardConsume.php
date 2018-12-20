<?php

namespace App\Entity\CreditCard;

use App\Entity\Creditcard\CreditRelation;
use App\Entity\Security\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CreditCard\CreditCardConsumeRepository")
 */
class CreditCardConsume
{
    const STATUS_CREATED = 0;
    const STATUS_PAYING = 1;
    const STATUS_MORA = 2;
    const STATUS_PAYED = 3;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Security\User", inversedBy="creditCardConsumes")
     */
    private $user;

    /**
     * @ORM\Column(type="float")
     */
    private $amount;

    /**
     * @ORM\Column(type="smallint")
     */
    private $dues;

    /**
     * @ORM\Column(type="float")
     */
    private $interest;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $code;

    /**
     * @ORM\Column(type="boolean")
     */
    private $status;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $update_at;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $delete_at;

    /**
     * @ORM\Column(type="date")
     */
    private $consume_at;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\CreditCard\CreditRelation", mappedBy="consume", cascade={"persist", "remove"})
     */
    private $creditRelation;

    /**
     * CreditCardConsume constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->created_at = new \DateTime('now');
        $this->status = self::STATUS_CREATED;
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

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getDues(): ?int
    {
        return $this->dues;
    }

    public function setDues(int $dues): self
    {
        $this->dues = $dues;

        return $this;
    }

    public function getInterest(): ?float
    {
        return $this->interest;
    }

    public function setInterest(float $interest): self
    {
        $this->interest = $interest;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): self
    {
        $this->code = $code;

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

    public function getUpdateAt(): ?\DateTimeInterface
    {
        return $this->update_at;
    }

    public function setUpdateAt(?\DateTimeInterface $update_at): self
    {
        $this->update_at = $update_at;

        return $this;
    }

    public function getDeleteAt(): ?\DateTimeInterface
    {
        return $this->delete_at;
    }

    public function setDeleteAt(?\DateTimeInterface $delete_at): self
    {
        $this->delete_at = $delete_at;

        return $this;
    }

    public function getStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getConsumeAt(): ?\DateTimeInterface
    {
        return $this->consume_at;
    }

    public function setConsumeAt(\DateTimeInterface $consume_at): self
    {
        $this->consume_at = $consume_at;

        return $this;
    }

    public function getCreditRelation(): ?CreditRelation
    {
        return $this->creditRelation;
    }

    public function setCreditRelation(CreditRelation $creditRelation): self
    {
        $this->creditRelation = $creditRelation;

        // set the owning side of the relation if necessary
        if ($this !== $creditRelation->getConsume()) {
            $creditRelation->setConsume($this);
        }

        return $this;
    }
}
