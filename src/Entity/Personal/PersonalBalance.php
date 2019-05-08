<?php

namespace App\Entity\Personal;

use App\Entity\Security\User;
use App\Util\TimestampAbleEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * BalanceGeneral
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="App\Repository\Personal\PersonalBalanceRepository")
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Security\User", inversedBy="id")
     * @ORM\JoinColumn(nullable=false)
     * */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=false)
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

    use TimestampAbleEntity;

    public function getId(): ?bool
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getMonth(): ?string
    {
        return $this->month;
    }

    public function setMonth(string $month): self
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

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user): void
    {
        $this->user = $user;
    }

}
