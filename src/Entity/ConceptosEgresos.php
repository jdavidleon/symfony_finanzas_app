<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ConceptosEgresos
 *
 * @ORM\Table(name="conceptos_egresos", uniqueConstraints={@ORM\UniqueConstraint(name="concepto", columns={"concepto"})})
 * @ORM\Entity
 */
class ConceptosEgresos
{
    /**
     * @var bool
     *
     * @ORM\Column(name="id_concepto_egreso", type="boolean", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idConceptoEgreso;

    /**
     * @var string
     *
     * @ORM\Column(name="concepto", type="string", length=100, nullable=false)
     */
    private $concepto;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="tm_delete", type="datetime", nullable=true)
     */
    private $tmDelete;

    public function getIdConceptoEgreso(): ?bool
    {
        return $this->idConceptoEgreso;
    }

    public function getConcepto(): ?string
    {
        return $this->concepto;
    }

    public function setConcepto(string $concepto): self
    {
        $this->concepto = $concepto;

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
