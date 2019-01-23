<?php

namespace App\Entity\Personal;

use App\Util\TimestampableEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Balance
 *
 * @ORM\Table()
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
     * @var float
     *
     * @ORM\Column(type="float", precision=10, scale=0, nullable=false)
     */
    private $debt;

    /**
     * @var float|null
     *
     * @ORM\Column(type="float", precision=10, scale=0, nullable=true)
     */
    private $payed;

    /**
     *
     * @ORM\Column(type="float")
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
