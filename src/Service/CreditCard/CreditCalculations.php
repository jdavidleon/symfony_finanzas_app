<?php
/**
 * Created by PhpStorm.
 * User: JLEON
 * Date: 21/12/2018
 * Time: 3:59 PM
 */

namespace App\Service\CreditCard;


class CreditCalculations
{
    /**
     * @param float $payments
     * @param float $amount
     * @return float
     */
    public function calculateActualCreditCardConsumeDebt(float $amount, float $payments): float
    {
        if ($amount <= $payments)
            return 0;

        return $amount - $payments;
    }
    /*
     * TODO: se debe lanzar una excepción cuando actualDebt > 0 && pendingDues = 0?
     * */
    public function calculateNextCapitalAmount($actualDebt, $pendingDues)
    {
        if (0 >= $pendingDues){
            return 0;
        }

        return $actualDebt / $pendingDues;
    }

    public function calculateNextInterestAmount($actualDebt, $interest)
    {
        return ( $actualDebt * $interest ) / 100;
    }

    public function calculateNextPaymentAmount($nextCapitalAmount, $nextInterestAmount)
    {
        return $nextCapitalAmount + $nextInterestAmount;
    }
    
    public function calculateNumberOfPendingDues(int $totalDues, int $payedDues = 0): int
    {
        return $totalDues - $payedDues;
    }

    public function calculateActualDueToPay(int $totalDues, int $pendingDues): int
    {
        return $totalDues - $pendingDues + 1;
    }

    /**
     * Return the list of payment that have to pay
     * @param float $actualDebt
     * @param float $interest
     * @param int $totalDues
     * @param int $payedDues
     * @return array
     */
    public function calculatePendingPaymentsResume(
        float $actualDebt,
        float $interest,
        int $totalDues,
        int $payedDues = 0
    ): array
    {
        if ($totalDues <= $payedDues){
            return [];
        }

        $capitalMonthlyAmount = $this->calculateNextCapitalAmount(
            $actualDebt,
            $this->calculateNumberOfPendingDues($totalDues, $payedDues)
        );

        $duesToPay = [];
        foreach ( range($payedDues + 1, $totalDues ) as $due){
            $interestToPay = ( ($actualDebt * $interest) / 100 );
            $duesToPay[] = [
                'number_due' => $due,
                'capital_amount' => $capitalMonthlyAmount,
                'interest' => $interestToPay,
                'total_to_pay' =>  $capitalMonthlyAmount + $interestToPay
            ];
            $actualDebt -= $capitalMonthlyAmount;
        }

        return $duesToPay;
    }

    public function sumArrayValues(array $values = [])
    {
        return array_sum( $values );
    }

    /**
     * @return string
     */
    public function calculateNextPaymentDate()
    {
        $actualMonth = date('d-m-Y');
        $nextMonth = date("m-Y", strtotime($actualMonth . "+ 1 Month"));
        return date('j') < 15 ? substr($actualMonth, 3) : $nextMonth;
    }

    public function calculateHandlingFee($handlingFee, $cardUsers)
    {
        return $handlingFee / $cardUsers;
    }
}

