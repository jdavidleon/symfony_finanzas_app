<?php
/**
 * Created by PhpStorm.
 * User: JLEON
 * Date: 21/12/2018
 * Time: 3:59 PM
 */

namespace App\Service\CreditCard;


use App\Entity\CreditCard\CreditCardConsume;
use App\Repository\CreditCard\CreditCardConsumeRepository;

class CreditCalculations
{

    /**
     * @var CreditCardConsumeRepository
     */
    private $cardConsumeRepository;

    public function __construct(
        CreditCardConsumeRepository $cardConsumeRepository
    )
    {
        $this->cardConsumeRepository = $cardConsumeRepository;
    }


    /**
     * @param CreditCardConsume $creditCardConsume
     */
    public function getQuotaByConsume(CreditCardConsume $creditCardConsume)
    {
        $payments = $creditCardConsume->getPayments();

        $saldo = $creditCardConsume->getAmount() - $this->getBalanceDebt();
        $pendingDues = $this->getPendingDues();
        
    }

    public function getPendingDues(CreditCardConsume $creditCardConsume)
    {
        return $creditCardConsume->getDues() - count( $creditCardConsume->getPayments() );
    }

    public function getBalanceDebt(CreditCardConsume $payments, $amount)
    {
        $payed = 0;
        /* @var CreditCardConsume $payments */
        foreach ( $payments as $pay ){
            $payed += $pay->getCapitalAmount();
        }

        return $amount - $payed;
    }
    
}

