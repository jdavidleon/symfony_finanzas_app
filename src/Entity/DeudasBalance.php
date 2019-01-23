<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DeudasBalance
 *
 * @ORM\Table(name="deudas_balance", indexes={@ORM\Index(name="id_deuda", columns={"id_deuda"})})
 * @ORM\Entity
 */
class DeudasBalance
{
    /**
     * @var int
     *
     * @ORM\Column(name="id_deuda_balance", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idDeudaBalance;

    /**
     * @var int
     *
     * @ORM\Column(name="id_deuda", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $idDeuda;

    /**
     * @var int
     *
     * @ORM\Column(name="abonos_capital", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $abonosCapital = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="interes_pagado", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $interesPagado = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="cuotas_pagas", type="smallint", nullable=false, options={"unsigned"=true})
     */
    private $cuotasPagas = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="saldo", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $saldo;

    /**
     * @var int|null
     *
     * @ORM\Column(name="mora", type="integer", nullable=true)
     */
    private $mora = '0';

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

    public function getIdDeudaBalance(): ?int
    {
        return $this->idDeudaBalance;
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

    public function getAbonosCapital(): ?int
    {
        return $this->abonosCapital;
    }

    public function setAbonosCapital(int $abonosCapital): self
    {
        $this->abonosCapital = $abonosCapital;

        return $this;
    }

    public function getInteresPagado(): ?int
    {
        return $this->interesPagado;
    }

    public function setInteresPagado(int $interesPagado): self
    {
        $this->interesPagado = $interesPagado;

        return $this;
    }

    public function getCuotasPagas(): ?int
    {
        return $this->cuotasPagas;
    }

    public function setCuotasPagas(int $cuotasPagas): self
    {
        $this->cuotasPagas = $cuotasPagas;

        return $this;
    }

    public function getSaldo(): ?int
    {
        return $this->saldo;
    }

    public function setSaldo(int $saldo): self
    {
        $this->saldo = $saldo;

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
