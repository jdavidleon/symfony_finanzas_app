<?php

namespace App\Entity\Debts;

use App\Util\TimestampableEntity;
use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\Types\Self_;

/**
 * FixedCharges
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class FixedCharges
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Security\User", inversedBy="id")
     * @ORM\JoinColumn(nullable=false)
     * */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100, nullable=false)
     */
    private $concept;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=false, options={"unsigned"=true})
     */
    private $value;

    /**
     *
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $payDay;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $status = self::OPEN;

    /**
     *
     * @ORM\Column(type="date", nullable=true)
     */
    private $lastPayedMonth;

    use TimestampableEntity;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getConcept(): ?string
    {
        return $this->concept;
    }

    public function setConcept(string $concept): self
    {
        $this->concept = $concept;

        return $this;
    }

    public function getValue(): ?int
    {
        return $this->value;
    }

    public function setValue(int $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getPayDay(): ?bool
    {
        return $this->payDay;
    }

    public function setPayDay(?bool $payDay): self
    {
        $this->payDay = $payDay;

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

    public function getLastPayedMonth(): ?string
    {
        return $this->lastPayedMonth;
    }

    public function setLastPayedMonth(?string $lastPayedMonth): self
    {
        $this->lastPayedMonth = $lastPayedMonth;

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
