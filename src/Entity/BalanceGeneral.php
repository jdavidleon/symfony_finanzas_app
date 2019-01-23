<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BalanceGeneral
 *
 * @ORM\Table(name="balance_general")
 * @ORM\Entity
 */
class BalanceGeneral
{
    /**
     * @var bool
     *
     * @ORM\Column(name="id_balance", type="boolean", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idBalance;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="mes", type="date", nullable=true)
     */
    private $mes;

    /**
     * @var int
     *
     * @ORM\Column(name="ingresos", type="integer", nullable=false)
     */
    private $ingresos;

    /**
     * @var int
     *
     * @ORM\Column(name="egresos", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $egresos;

    /**
     * @var int
     *
     * @ORM\Column(name="dinero_actual", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $dineroActual;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="tm_delete", type="datetime", nullable=true)
     */
    private $tmDelete;

    public function getIdBalance(): ?bool
    {
        return $this->idBalance;
    }

    public function getMes(): ?\DateTimeInterface
    {
        return $this->mes;
    }

    public function setMes(?\DateTimeInterface $mes): self
    {
        $this->mes = $mes;

        return $this;
    }

    public function getIngresos(): ?int
    {
        return $this->ingresos;
    }

    public function setIngresos(int $ingresos): self
    {
        $this->ingresos = $ingresos;

        return $this;
    }

    public function getEgresos(): ?int
    {
        return $this->egresos;
    }

    public function setEgresos(int $egresos): self
    {
        $this->egresos = $egresos;

        return $this;
    }

    public function getDineroActual(): ?int
    {
        return $this->dineroActual;
    }

    public function setDineroActual(int $dineroActual): self
    {
        $this->dineroActual = $dineroActual;

        return $this;
    }

    public function getTmDelete(): ?\DateTimeInterface
    {
        return $this->tmDelete;
    }

    public function setTmDelete(?\DateTimeInterface $tmDelete): self
    {
        $this->tmDelete = $tmDelete;

        return $this;
    }


}
