<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Egresos
 *
 * @ORM\Table(name="egresos", uniqueConstraints={@ORM\UniqueConstraint(name="valor", columns={"valor"}), @ORM\UniqueConstraint(name="id_concepto_egreso", columns={"id_concepto_egreso"})})
 * @ORM\Entity
 */
class Egresos
{
    /**
     * @var bool
     *
     * @ORM\Column(name="id_egresos", type="boolean", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idEgresos;

    /**
     * @var int
     *
     * @ORM\Column(name="id_concepto_egreso", type="smallint", nullable=false, options={"unsigned"=true})
     */
    private $idConceptoEgreso;

    /**
     * @var string
     *
     * @ORM\Column(name="detalle_concepto", type="string", length=5, nullable=false)
     */
    private $detalleConcepto;

    /**
     * @var int
     *
     * @ORM\Column(name="valor", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $valor;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="fecha", type="datetime", nullable=true, options={"default"="CURRENT_TIMESTAMP"})
     */
    private $fecha = 'CURRENT_TIMESTAMP';

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="tm_delete", type="datetime", nullable=true)
     */
    private $tmDelete;

    public function getIdEgresos(): ?bool
    {
        return $this->idEgresos;
    }

    public function getIdConceptoEgreso(): ?int
    {
        return $this->idConceptoEgreso;
    }

    public function setIdConceptoEgreso(int $idConceptoEgreso): self
    {
        $this->idConceptoEgreso = $idConceptoEgreso;

        return $this;
    }

    public function getDetalleConcepto(): ?string
    {
        return $this->detalleConcepto;
    }

    public function setDetalleConcepto(string $detalleConcepto): self
    {
        $this->detalleConcepto = $detalleConcepto;

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

    public function getFecha(): ?\DateTimeInterface
    {
        return $this->fecha;
    }

    public function setFecha(?\DateTimeInterface $fecha): self
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
