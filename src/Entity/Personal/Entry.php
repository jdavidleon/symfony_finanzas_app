<?php

namespace App\Entity\Personal;

use App\Util\TimestampableEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Entry
 *
 * @ORM\Table(name="ingresos")
 * @ORM\Entity
 */
class Entry
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
     * @ORM\Column(name="concepto", type="string", length=35, nullable=false)
     */
    private $concept;

    /**
     * @var int
     *
     * @ORM\Column(name="valor", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $value;

    use TimestampableEntity;

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

    public function getValue(): ?int
    {
        return $this->value;
    }

    public function setValue(int $value): self
    {
        $this->value = $value;

        return $this;
    }
}
