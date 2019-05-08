<?php

namespace App\Entity\CreditCard;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CreditCard\HandlingFeeRepository")
 */
class HandlingFee
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CreditCard\CreditCard")
     * @ORM\JoinColumn(nullable=false)
     */
    private $CreditCard;

    /**
     * @ORM\Column(type="float")
     */
    private $fee;

    /**
     * @ORM\Column(type="boolean")
     */
    private $active;

    public function __construct()
    {
        $this->active = true;
    }

    public function getCreditCard(): ?CreditCard
    {
        return $this->CreditCard;
    }

    public function setCreditCard(?CreditCard $CreditCard): self
    {
        $this->CreditCard = $CreditCard;

        return $this;
    }

    public function getFee(): ?float
    {
        return $this->fee;
    }

    public function setFee(float $fee): self
    {
        $this->fee = $fee;

        return $this;
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function inActivate()
    {
        $this->active = false;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }
}
