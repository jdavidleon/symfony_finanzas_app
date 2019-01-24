<?php

namespace App\Entity\Debts;

use App\Util\TimestampableEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * DebtsBalance
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class DebtsBalance
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var float
     *
     * @ORM\Column(type="float", precision=10, scale=0, nullable=false)
     */
    private $debt;

    /**
     * @var float|null
     *
     * @ORM\Column(nullable=true)
     */
    private $payed;

    /**
     * @var float|null
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $interestPayed;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=false)
     */
    private $initialDues;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=false)
     */
    private $pendingDues;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=30,nullable=true)
     */
    private $lastPayedMonth;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=false)
     * */
    private $balance;

    use TimestampableEntity;

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

    /**
     * @return mixed
     */
    public function getBalance()
    {
        return $this->balance;
    }

    /**
     * @param mixed $balance
     */
    public function setBalance($balance): void
    {
        $this->balance = $balance;
    }


}
