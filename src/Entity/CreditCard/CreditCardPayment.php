<?php

namespace App\Entity\CreditCard;

use App\Util\TimestampAbleEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Todo: Como se van a revertir pagos??? Con respecto a la relación con el balance del creditCardConsume
 * @ORM\Entity(repositoryClass="App\Repository\CreditCard\CreditCardPaymentRepository")
 */
class CreditCardPayment
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CreditCard\CreditCardConsume", inversedBy="payments")
     * @ORM\JoinColumn(nullable=false)
     */
    private $creditConsume;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $due;

    /**
     * @ORM\Column(type="string", nullable=true)
     * */
    private $monthPayed;

    /**
     * @ORM\Column(type="float")
     */
    private $capitalAmount;

    /**
     * @ORM\Column(type="float")
     * */
    private $realCapitalAmount;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $interestAmount;

    /**
     * @ORM\Column(type="float")
     */
    private $totalAmount;

    /**
     * @ORM\Column(type="boolean")
     * */
    private $legalDue = true;

    use TimestampAbleEntity;

    public function __construct(
        CreditCardConsume $cardConsume
    ) {
        $this->creditConsume = $cardConsume;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCapitalAmount(): ?float
    {
        return $this->capitalAmount;
    }

    public function setCapitalAmount(float $capitalAmount): self
    {
        $this->capitalAmount = $capitalAmount;

        return $this;
    }

    public function getInterestAmount(): ?float
    {
        return $this->interestAmount;
    }

    public function setInterestAmount(?float $interestAmount): self
    {
        $this->interestAmount = $interestAmount;

        return $this;
    }

    public function getTotalAmount(): ?float
    {
        return $this->totalAmount;
    }

    public function setTotalAmount(float $totalAmount): self
    {
        $this->totalAmount = $totalAmount;

        return $this;
    }

    public function setCreditConsume($consume)
    {
        $this->creditConsume = $consume;

        return $this;
    }

    public function getCreditConsume(): ?CreditCardConsume
    {
        return $this->creditConsume;
    }

    /**
     * @return mixed
     */
    public function isLegalDue()
    {
        return $this->legalDue;
    }


    /**
     * @param bool $legalDue
     * @return CreditCardPayment
     */
    public function setLegalDue(bool $legalDue): self
    {
        $this->legalDue = $legalDue;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMonthPayed()
    {
        return $this->monthPayed;
    }

    /**
     * @param string|null $monthPayed
     * @return CreditCardPayment
     */
    public function setMonthPayed(?string $monthPayed): self
    {
        $this->monthPayed = $monthPayed;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRealCapitalAmount()
    {
        return $this->realCapitalAmount;
    }

    /**
     * @param float|null $realCapitalAmount
     * @return CreditCardPayment
     */
    public function setRealCapitalAmount(?float $realCapitalAmount): self
    {
        $this->realCapitalAmount = $realCapitalAmount;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDue()
    {
        return $this->due;
    }

    /**
     * @param int|null $due
     * @return CreditCardPayment
     */
    public function setDue(?int $due): self
    {
        $this->due = $due;

        return $this;
    }
}
