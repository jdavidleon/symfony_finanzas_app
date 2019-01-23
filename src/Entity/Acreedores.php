<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Acreedores
 *
 * @ORM\Table(name="acreedores", uniqueConstraints={@ORM\UniqueConstraint(name="banco", columns={"banco"})})
 * @ORM\Entity
 */
class Acreedores
{
    /**
     * @var int
     *
     * @ORM\Column(name="id_acreedor", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idAcreedor;

    /**
     * @var string
     *
     * @ORM\Column(name="banco", type="string", length=80, nullable=false)
     */
    private $banco;

    /**
     * @var string
     *
     * @ORM\Column(name="propietario", type="string", length=100, nullable=false)
     */
    private $propietario;

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

    public function getIdAcreedor(): ?int
    {
        return $this->idAcreedor;
    }

    public function getBanco(): ?string
    {
        return $this->banco;
    }

    public function setBanco(string $banco): self
    {
        $this->banco = $banco;

        return $this;
    }

    public function getPropietario(): ?string
    {
        return $this->propietario;
    }

    public function setPropietario(string $propietario): self
    {
        $this->propietario = $propietario;

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
