<?php

namespace App\Entity\Debts;

use App\Entity\Security\User;
use App\Util\TimestampAbleEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Debts
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="App\Repository\Debts\CreditRepository")
 */
class Credits
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Security\User")
     * */
    private $user;

    /**
     * @var int
     * @ORM\ManyToOne(targetEntity="App\Entity\Debts\Creditor", inversedBy="id")
     */
    private $creditor;
        /* TODO: Definir Concepto de deudas y gastos*/

//    /**
//     * @ORM\ManyToOne(targetEntity="")
//     * @ORM\JoinColumn(nullable=false)
//     */
//    private $debtType;

    /**
     * @var string|null
     *
     * @ORM\Column(name="concepto", type="string", length=150, nullable=true)
     */
    private $concept;

    /**
     * @var int
     *
     * @ORM\Column(name="valor", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $value;

    /**
     * @var float|null
     *
     * @ORM\Column(type="float", precision=10, scale=0, nullable=true)
     */
    private $rate;

    /**
     * @var bool
     *
     * @ORM\Column(name="cuotas", type="boolean", nullable=false)
     */
    private $dues;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="date", nullable=false)
     */
    private $paymentDay;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="date", nullable=true)
     */
    private $firstPaymentDay;

    /**
     * @ORM\OneToOne(targetEntity="CreditsBalance", mappedBy="balance")
     * */
    private $balance;

    use TimestampAbleEntity;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreditor(): ?int
    {
        return $this->creditor;
    }

    public function setCreditor(int $creditor): self
    {
        $this->creditor = $creditor;

        return $this;
    }

    public function getConcept(): ?string
    {
        return $this->concept;
    }

    public function setConcept(?string $concept): self
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

    public function getRate(): ?float
    {
        return $this->rate;
    }

    public function setRate(?float $rate): self
    {
        $this->rate = $rate;

        return $this;
    }

    public function getDues(): ?bool
    {
        return $this->dues;
    }

    public function setDues(bool $dues): self
    {
        $this->dues = $dues;

        return $this;
    }

    public function getPaymentDay(): ?string
    {
        return $this->paymentDay;
    }

    public function setPaymentDay(bool $paymentDay): self
    {
        $this->paymentDay = $paymentDay;

        return $this;
    }

    public function getFirstPaymentDay(): ?\DateTimeInterface
    {
        return $this->firstPaymentDay;
    }

    public function setFirstPaymentDay(?\DateTimeInterface $firstPaymentDay): self
    {
        $this->firstPaymentDay = $firstPaymentDay;

        return $this;
    }

    public function getDebtType(): ?FixedCharges
    {
        return $this->debtType;
    }

    public function setDebtType(?FixedCharges $debtType): self
    {
        $this->debtType = $debtType;

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
