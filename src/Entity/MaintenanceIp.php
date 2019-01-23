<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MaintenanceIp
 *
 * @ORM\Table(name="maintenance_ip", uniqueConstraints={@ORM\UniqueConstraint(name="ip_address", columns={"ip_address"})}, indexes={@ORM\Index(name="id_maintenance", columns={"id_maintenance"})})
 * @ORM\Entity
 */
class MaintenanceIp
{
    /**
     * @var bool
     *
     * @ORM\Column(name="id_ip", type="boolean", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idIp;

    /**
     * @var bool
     *
     * @ORM\Column(name="id_maintenance", type="boolean", nullable=false)
     */
    private $idMaintenance;

    /**
     * @var string
     *
     * @ORM\Column(name="ip_address", type="string", length=50, nullable=false)
     */
    private $ipAddress;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="tm_delete", type="datetime", nullable=true)
     */
    private $tmDelete;

    public function getIdIp(): ?bool
    {
        return $this->idIp;
    }

    public function getIdMaintenance(): ?bool
    {
        return $this->idMaintenance;
    }

    public function setIdMaintenance(bool $idMaintenance): self
    {
        $this->idMaintenance = $idMaintenance;

        return $this;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(string $ipAddress): self
    {
        $this->ipAddress = $ipAddress;

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
