<?php
/**
 * Created by PhpStorm.
 * User: IT OPTIME
 * Date: 29/01/2019
 * Time: 8:53 AM
 */

namespace App\Service\Debts;


use App\Entity\Debts\Credits;
use App\Entity\Debts\CreditsBalance;
use Doctrine\ORM\EntityManagerInterface;

class DebtsHandlers
{

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    )
    {
        $this->entityManager = $entityManager;
    }
    
    /**
     * @param Credits $debt
     * @throws \Exception
     */
    public function setBalanceDebt(Credits $debt)
    {
        $balance = new CreditsBalance();
        $balance->setDebt($debt->getId());
        $balance->setValue($debt->getValue());
        $balance->setInitialDues($debt->getDues());
        $balance->setPendingDues($debt->getDues());
        $balance->setBalance($debt->getValue());
        $this->entityManager->persist($balance);
        $this->entityManager->flush();
    }
}