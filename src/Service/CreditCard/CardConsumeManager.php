<?php


namespace App\Service\CreditCard;


use App\Entity\CreditCard\CreditCardPayment;
use Doctrine\ORM\EntityManagerInterface;
use function PHPUnit\Framework\StaticAnalysis\HappyPath\AssertIsInt\consume;

class CardConsumeManager
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(
        EntityManagerInterface $em
    )
    {
        $this->em = $em;
    }

    public function balanceManagement(CreditCardPayment $payment)
    {
        $consume = $payment->getCreditConsume();
        $consume->setAmountPayed(
            $consume->getAmountPayed() + $payment->getCapitalAmount()
        );

        if ($payment->isLegalDue()) {
            $consume->setDuesPayed($consume->getDuesPayed() + 1);
        }

        $consume->changeStatusToPayed();

        $this->em->persist($consume);
    }

}