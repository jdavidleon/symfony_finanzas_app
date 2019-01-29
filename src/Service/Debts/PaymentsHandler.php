<?php
/**
 * Created by PhpStorm.
 * User: IT OPTIME
 * Date: 29/01/2019
 * Time: 11:08 AM
 */

namespace App\Service\Debts;


use App\Entity\Debts\CreditsBalance;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

class PaymentsHandler
{

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(
        EntityManager $entityManager
    )
    {
        $this->entityManager = $entityManager;
    }

    public function newPayment(CreditsBalance $balance)
    {

    }

    public function loanPayment()
    {

    }
}