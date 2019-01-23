<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Deudas
 *
 * @ORM\Table(name="deudas", indexes={@ORM\Index(name="id_estado_deuda", columns={"id_estado_deuda"}), @ORM\Index(name="id_acreedor", columns={"id_acreedor"})})
 * @ORM\Entity
 */
class Deudas
{
    /**
     * @var int
     *
     * @ORM\Column(name="id_deuda", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idDeuda;

    /**
     * @var int
     *
     * @ORM\Column(name="id_acreedor", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $idAcreedor;

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
    private $valor;

    /**
     * @var float|null
     *
     * @ORM\Column(name="tasa", type="float", precision=10, scale=0, nullable=true)
     */
    private $tasa;

    /**
     * @var bool
     *
     * @ORM\Column(name="cuotas", type="boolean", nullable=false)
     */
    private $cuotas;

    /**
     * @var bool
     *
     * @ORM\Column(name="dia_pago", type="boolean", nullable=false)
     */
    private $diaPago;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="primer_pago", type="date", nullable=true)
     */
    private $primerPago;

    /**
     * @var bool
     *
     * @ORM\Column(name="id_estado_deuda", type="boolean", nullable=false, options={"default"="1"})
     */
    private $idEstadoDeuda = '1';

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

    public function getIdDeuda(): ?int
    {
        return $this->idDeuda;
    }

    public function getIdAcreedor(): ?int
    {
        return $this->idAcreedor;
    }

    public function setIdAcreedor(int $idAcreedor): self
    {
        $this->idAcreedor = $idAcreedor;

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

    public function getValor(): ?int
    {
        return $this->valor;
    }

    public function setValor(int $valor): self
    {
        $this->valor = $valor;

        return $this;
    }

    public function getTasa(): ?float
    {
        return $this->tasa;
    }

    public function setTasa(?float $tasa): self
    {
        $this->tasa = $tasa;

        return $this;
    }

    public function getCuotas(): ?bool
    {
        return $this->cuotas;
    }

    public function setCuotas(bool $cuotas): self
    {
        $this->cuotas = $cuotas;

        return $this;
    }

    public function getDiaPago(): ?bool
    {
        return $this->diaPago;
    }

    public function setDiaPago(bool $diaPago): self
    {
        $this->diaPago = $diaPago;

        return $this;
    }

    public function getPrimerPago(): ?\DateTimeInterface
    {
        return $this->primerPago;
    }

    public function setPrimerPago(?\DateTimeInterface $primerPago): self
    {
        $this->primerPago = $primerPago;

        return $this;
    }

    public function getIdEstadoDeuda(): ?bool
    {
        return $this->idEstadoDeuda;
    }

    public function setIdEstadoDeuda(bool $idEstadoDeuda): self
    {
        $this->idEstadoDeuda = $idEstadoDeuda;

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
