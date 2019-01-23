<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Pagos
 *
 * @ORM\Table(name="pagos", indexes={@ORM\Index(name="id_cargo_fijo", columns={"id_cargo_fijo"}), @ORM\Index(name="id_deuda", columns={"id_deuda"})})
 * @ORM\Entity
 */
class Pagos
{
    /**
     * @var int
     *
     * @ORM\Column(name="id_pagos", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idPagos;

    /**
     * @var int
     *
     * @ORM\Column(name="id_deuda", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $idDeuda = '0';

    /**
     * @var int|null
     *
     * @ORM\Column(name="n_cuota", type="smallint", nullable=true)
     */
    private $nCuota;

    /**
     * @var int
     *
     * @ORM\Column(name="id_cargo_fijo", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $idCargoFijo = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="abono_capital", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $abonoCapital;

    /**
     * @var int
     *
     * @ORM\Column(name="pago_intereses", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $pagoIntereses = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="pago_total", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $pagoTotal;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     */
    private $fecha = 'CURRENT_TIMESTAMP';

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="tm_delete", type="datetime", nullable=true)
     */
    private $tmDelete;

    public function getIdPagos(): ?int
    {
        return $this->idPagos;
    }

    public function getIdDeuda(): ?int
    {
        return $this->idDeuda;
    }

    public function setIdDeuda(int $idDeuda): self
    {
        $this->idDeuda = $idDeuda;

        return $this;
    }

    public function getNCuota(): ?int
    {
        return $this->nCuota;
    }

    public function setNCuota(?int $nCuota): self
    {
        $this->nCuota = $nCuota;

        return $this;
    }

    public function getIdCargoFijo(): ?int
    {
        return $this->idCargoFijo;
    }

    public function setIdCargoFijo(int $idCargoFijo): self
    {
        $this->idCargoFijo = $idCargoFijo;

        return $this;
    }

    public function getAbonoCapital(): ?int
    {
        return $this->abonoCapital;
    }

    public function setAbonoCapital(int $abonoCapital): self
    {
        $this->abonoCapital = $abonoCapital;

        return $this;
    }

    public function getPagoIntereses(): ?int
    {
        return $this->pagoIntereses;
    }

    public function setPagoIntereses(int $pagoIntereses): self
    {
        $this->pagoIntereses = $pagoIntereses;

        return $this;
    }

    public function getPagoTotal(): ?int
    {
        return $this->pagoTotal;
    }

    public function setPagoTotal(int $pagoTotal): self
    {
        $this->pagoTotal = $pagoTotal;

        return $this;
    }

    public function getFecha(): ?\DateTimeInterface
    {
        return $this->fecha;
    }

    public function setFecha(\DateTimeInterface $fecha): self
    {
        $this->fecha = $fecha;

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
