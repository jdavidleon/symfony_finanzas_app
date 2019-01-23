<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Maintenance
 *
 * @ORM\Table(name="maintenance")
 * @ORM\Entity
 */
class Maintenance
{
    /**
     * @var bool
     *
     * @ORM\Column(name="id_maintenance", type="boolean", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idMaintenance;

    /**
     * @var string
     *
     * @ORM\Column(name="state", type="string", length=60, nullable=false)
     */
    private $state;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="tm_maintenance", type="datetime", nullable=true)
     */
    private $tmMaintenance;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="tm_delete", type="datetime", nullable=true)
     */
    private $tmDelete;

    public function getIdMaintenance(): ?bool
    {
        return $this->idMaintenance;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(string $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getTmMaintenance(): ?\DateTimeInterface
    {
        return $this->tmMaintenance;
    }

    public function setTmMaintenance(?\DateTimeInterface $tmMaintenance): self
    {
        $this->tmMaintenance = $tmMaintenance;

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
