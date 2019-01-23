<?php

namespace App\Entity\Personal;

use App\Util\TimestampableEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Egress
 *
 * @ORM\Table(name="Egress")
 * @ORM\Entity
 */
class Egress
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=false, options={"unsigned"=true})
     */
    private $value;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=false, options={"unsigned"=true})
     */
    private $concept;

    use TimestampableEntity;

    public function getId(): ?bool
    {
        return $this->id;
    }

    public function getConcept(): ?int
    {
        return $this->concept;
    }

    public function setConcept(int $concept): self
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
