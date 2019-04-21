<?php

namespace App\Entity\Personal;

use App\Entity\Security\User;
use App\Util\TimestampAbleEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Egress
 *
 * @ORM\Table(name="Egress")
 * @ORM\Entity(repositoryClass="App\Repository\Personal\EgressRepository")
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Security\User", inversedBy="id")
     * @ORM\JoinColumn(nullable=false)
     * */
    private $user;

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

    use TimestampAbleEntity;

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

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user): void
    {
        $this->user = $user;
    }
}
