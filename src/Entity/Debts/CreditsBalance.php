<?php

namespace App\Entity\Debts;

use App\Util\TimestampAbleEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * DebtsBalance
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="")
 */
class CreditsBalance
{
    const INVALID = 0;
    const OPEN = 1;
    const PAYING = 2;
    const MORA = 3;
    const PAYED = 4;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var float
     *
     * @ORM\OneToOne(targetEntity="Credits", inversedBy="id")
     */
    private $debt;

    /**
     * @ORM\Column(type="float")
     * */
    private $value;

    /**
     * @var float|null
     *
     * @ORM\Column(nullable=true)
     */
    private $payed = 0;

    /**
     * @var float|null
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $interestPayed = 0;

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

    /**
     * @ORM\Column(type="integer", nullable=false)
     * */
    private $status = self::OPEN;

    use TimestampAbleEntity;

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

    public function getInterestPayed(): ?float
    {
        return $this->interestPayed;
    }

    public function setInterestPayed(?float $interestPayed): self
    {
        $this->interestPayed = $interestPayed;

        return $this;
    }

    public function getInitialDues(): ?int
    {
        return $this->initialDues;
    }

    public function setInitialDues(int $initialDues): self
    {
        $this->initialDues = $initialDues;

        return $this;
    }

    public function getPendingDues(): ?int
    {
        return $this->pendingDues;
    }

    public function setPendingDues(int $pendingDues): self
    {
        $this->pendingDues = $pendingDues;

        return $this;
    }

    public function getLastPayedMonth(): ?string
    {
        return $this->lastPayedMonth;
    }

    public function setLastPayedMonth(?string $lastPayedMonth): self
    {
        $this->lastPayedMonth = $lastPayedMonth;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value): void
    {
        $this->value = $value;
    }


}
