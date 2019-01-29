<?php
/**
 * Created by PhpStorm.
 * User: IT OPTIME
 * Date: 29/01/2019
 * Time: 8:53 AM
 */

namespace App\Service\Debts;


use App\Entity\Debts\Debt;
use App\Entity\Debts\DebtsBalance;
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
     * @param Debt $debt
     * @throws \Exception
     */
    public function setBalanceDebt(Debt $debt)
    {
        $balance = new DebtsBalance();
        $balance->setDebt($debt->getId());
        $balance->setValue($debt->getValue());
        $balance->setInitialDues($debt->getDues());
        $balance->setPendingDues($debt->getDues());
        $balance->setBalance($debt->getValue());
        $this->entityManager->persist($balance);
        $this->entityManager->flush();
    }
}