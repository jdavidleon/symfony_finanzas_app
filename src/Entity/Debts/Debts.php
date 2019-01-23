<?php

namespace App\Entity\Debts;

use App\Util\TimestampableEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Debts
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Debts
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var int
     * @ORM\ManyToOne(targetEntity="App\Entity\Acreedores", inversedBy="")
     * @ORM\Column(name="id_acreedor", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $creditor;
        /* TODO: Definir Concepto de deudas y gastos*/
    /**
     * @var int
     *
     * @ORM\Column(name="id_concepto", type="smallint", nullable=false, options={"unsigned"=true})
     */
    private $idConcepto;

    /**
     * @var string|null
     *
     * @ORM\Column(name="concepto", type="string", length=150, nullable=true)
     */
    private $concepto;

    /**
     * @var int
     *
     * @ORM\Column(name="valor", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $value;

    /**
     * @var float|null
     *
     * @ORM\Column(name="tasa", type="float", precision=10, scale=0, nullable=true)
     */
    private $rate;

    /**
     * @var bool
     *
     * @ORM\Column(name="cuotas", type="boolean", nullable=false)
     */
    private $dues;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="date", nullable=false)
     */
    private $paymentDay;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="date", nullable=true)
     */
    private $firstPaymentDay;

    /**
     * @var bool
     *
     * @ORM\Column(name="id_estado_deuda", type="boolean", nullable=false, options={"default"="1"})
     */
    private $debtStatus = '1';

    use TimestampableEntity;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreditor(): ?int
    {
        return $this->creditor;
    }

    public function setCreditor(int $creditor): self
    {
        $this->creditor = $creditor;

        return $this;
    }

    public function getIdConcepto(): ?int
    {
        return $this->idConcepto;
    }

    public function setIdConcepto(int $idConcepto): self
    {
        $this->idConcepto = $idConcepto;

        return $this;
    }

    public function getConcepto(): ?string
    {
        return $this->concepto;
    }

    public function setConcepto(?string $concepto): self
    {
        $this->concepto = $concepto;

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

    public function getTasa(): ?float
    {
        return $this->rate;
    }

    public function setTasa(?float $tasa): self
    {
        $this->rate = $tasa;

        return $this;
    }

    public function getDues(): ?bool
    {
        return $this->dues;
    }

    public function setDues(bool $dues): self
    {
        $this->dues = $dues;

        return $this;
    }

    public function getPaymentDay(): ?bool
    {
        return $this->paymentDay;
    }

    public function setPaymentDay(bool $paymentDay): self
    {
        $this->paymentDay = $paymentDay;

        return $this;
    }

    public function getFirstPaymentDay(): ?\DateTimeInterface
    {
        return $this->firstPaymentDay;
    }

    public function setFirstPaymentDay(?\DateTimeInterface $firstPaymentDay): self
    {
        $this->firstPaymentDay = $firstPaymentDay;

        return $this;
    }

    public function getDebtStatus(): ?bool
    {
        return $this->debtStatus;
    }

    public function setDebtStatus(bool $debtStatus): self
    {
        $this->debtStatus = $debtStatus;

        return $this;
    }
}
