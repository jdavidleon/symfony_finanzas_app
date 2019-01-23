<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstadosDeuda
 *
 * @ORM\Table(name="estados_deuda")
 * @ORM\Entity
 */
class EstadosDeuda
{
    /**
     * @var int
     *
     * @ORM\Column(name="id_estado_deuda", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idEstadoDeuda;

    /**
     * @var string
     *
     * @ORM\Column(name="estados", type="string", length=20, nullable=false)
     */
    private $estados;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="tm_delete", type="datetime", nullable=true)
     */
    private $tmDelete;

    public function getIdEstadoDeuda(): ?int
    {
        return $this->idEstadoDeuda;
    }

    public function getEstados(): ?string
    {
        return $this->estados;
    }

    public function setEstados(string $estados): self
    {
        $this->estados = $estados;

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
