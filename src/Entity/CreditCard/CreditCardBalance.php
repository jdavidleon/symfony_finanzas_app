<?php

namespace App\Entity\CreditCard;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BalanceRepository")
 */
class CreditCardBalance
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

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
