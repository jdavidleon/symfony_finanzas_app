<?php

namespace App\Entity\Personal;

use App\Entity\Security\User;
use App\Util\TimestampAbleEntity;
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Security\User", inversedBy="id")
     * @ORM\JoinColumn(nullable=false)
     * */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=false)
     */
    private $concept;

    use TimestampAbleEntity;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }


}
