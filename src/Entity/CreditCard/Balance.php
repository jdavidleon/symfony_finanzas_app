<?php

namespace App\Entity\CreditCard;

use App\Entity\CreditCard\CreditRelation;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BalanceRepository")
 */
class Balance
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\CreditCard\CreditRelation", inversedBy="balance", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $CreditRelation;

    /**
     * @ORM\Column(type="float")
     */
    private $debt;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $payed;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreditRelation(): ?CreditRelation
    {
        return $this->CreditRelation;
    }

    public function setCreditRelation(CreditRelation $CreditRelation): self
    {
        $this->CreditRelation = $CreditRelation;

        return $this;
    }

    public function getDebt(): ?float
    {
        return $this->debt;
    }

    public function setDebt(float $debt): self
    {
        $this->debt = $debt;

        return $this;
    }

    public function getPayed(): ?float
    {
        return $this->payed;
    }

    public function setPayed(?float $payed): self
    {
        $this->payed = $payed;

        return $this;
    }
}
