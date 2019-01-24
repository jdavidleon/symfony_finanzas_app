<?php

namespace App\Entity\Personal;

use App\Util\TimestampableEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * EgressConcepts
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class EgressConcepts
{
    /**
     * @var bool
     *
     * @ORM\Column(name="id_concepto_egreso", type="boolean", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=false)
     */
    private $concept;

    use TimestampableEntity;

    public function getId(): ?bool
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


}
