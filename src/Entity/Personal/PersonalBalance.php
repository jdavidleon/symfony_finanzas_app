<?php

namespace App\Entity\Personal;

use App\Util\TimestampableEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * BalanceGeneral
 *
 * @ORM\Table(name="balance_general")
 * @ORM\Entity
 */
class PersonalBalance
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="mes", type="date", nullable=true)
     */
    private $month;

    /**
     * @var int
     *
     * @ORM\Column(name="ingresos", type="integer", nullable=false)
     */
    private $entries;

    /**
     * @var int
     *
     * @ORM\Column(name="egresos", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $egresses;

    /**
     * @var int
     *
     * @ORM\Column(name="dinero_actual", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $endMoney;

    use TimestampableEntity;

    public function getId(): ?bool
    {
        return $this->id;
    }

    public function getMonth(): ?\DateTimeInterface
    {
        return $this->month;
    }

    public function setMonth(?\DateTimeInterface $month): self
    {
        $this->month = $month;

        return $this;
    }

    public function getEntries(): ?int
    {
        return $this->entries;
    }

    public function setEntries(int $entries): self
    {
        $this->entries = $entries;

        return $this;
    }

    public function getEgresses(): ?int
    {
        return $this->egresses;
    }

    public function setEgresses(int $egresses): self
    {
        $this->egresses = $egresses;

        return $this;
    }

    public function getEndMoney(): ?int
    {
        return $this->endMoney;
    }

    public function setEndMoney(int $endMoney): self
    {
        $this->endMoney = $endMoney;

        return $this;
    }

}
