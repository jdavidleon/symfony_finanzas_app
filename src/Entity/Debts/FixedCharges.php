<?php

namespace App\Entity\Debts;

use Doctrine\ORM\Mapping as ORM;

/**
 * FixedCharges
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class FixedCharges
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100, nullable=false)
     */
    private $concept;

    /**
     * @var int
     *
     * @ORM\Column(name="valor", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $valor;

    /**
     * @var int|null
     *
     * @ORM\Column(name="mora", type="integer", nullable=true)
     */
    private $mora = '0';

    /**
     * @var bool|null
     *
     * @ORM\Column(name="dia_pago", type="boolean", nullable=true)
     */
    private $diaPago;

    /**
     * @var bool
     *
     * @ORM\Column(name="id_estado_deuda", type="boolean", nullable=false, options={"default"="1"})
     */
    private $idEstadoDeuda = '1';

    /**
     * @var string|null
     *
     * @ORM\Column(name="ultimo_mes_pagado", type="string", length=10, nullable=true, options={"fixed"=true})
     */
    private $ultimoMesPagado;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="tm_delete", type="datetime", nullable=true)
     */
    private $tmDelete;

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

    public function getValor(): ?int
    {
        return $this->valor;
    }

    public function setValor(int $valor): self
    {
        $this->valor = $valor;

        return $this;
    }

    public function getMora(): ?int
    {
        return $this->mora;
    }

    public function setMora(?int $mora): self
    {
        $this->mora = $mora;

        return $this;
    }

    public function getDiaPago(): ?bool
    {
        return $this->diaPago;
    }

    public function setDiaPago(?bool $diaPago): self
    {
        $this->diaPago = $diaPago;

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

    public function getUltimoMesPagado(): ?string
    {
        return $this->ultimoMesPagado;
    }

    public function setUltimoMesPagado(?string $ultimoMesPagado): self
    {
        $this->ultimoMesPagado = $ultimoMesPagado;

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
