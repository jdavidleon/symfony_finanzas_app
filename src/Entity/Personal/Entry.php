<?php

namespace App\Entity\Personal;

use App\Entity\Security\User;
use App\Util\TimestampAbleEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Entry
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="App\Repository\Personal\EntryRepository")
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Security\User", inversedBy="id")
     * @ORM\JoinColumn(nullable=false)
     * */
    private $user;

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

    use TimestampAbleEntity;

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

    /**
     * @return mixed
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return Entry
     */
    public function setUser(?User $user): self
    {
        $this->user = $user;
    }
}
