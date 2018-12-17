<?php

namespace App\Entity;

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
}
