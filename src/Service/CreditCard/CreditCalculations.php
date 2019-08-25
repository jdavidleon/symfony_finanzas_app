<?php
/**
 * Created by PhpStorm.
 * User: JLEON
 * Date: 21/12/2018
 * Time: 3:59 PM
 */

namespace App\Service\CreditCard;

use DateTime;
use Exception;

class CreditCalculations
{
    /**
     * @param float $payed
     * @param float $debt
     * @return float
     */
    public function calculateActualCreditCardConsumeDebt(float $debt, float $payed): float
    {
        if ($debt <= $payed)
            return 0;

        return $debt - $payed;
    }
    /*
     * TODO: se debe lanzar una excepción cuando actualDebt > 0 && pendingDues = 0?
     * */
    /**
     * @param float $actualDebt
     * @param int $pendingDues
     * @return float
     */
    public function calculateNextCapitalAmount(float $actualDebt, int $pendingDues): float
    {
        if (0 >= $pendingDues || $actualDebt < 0){
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
     * @param string|null $lastPayedMonth
     * @return array
     * @throws Exception
     */
    public function calculatePendingPaymentsResume(
        float $actualDebt,
        float $interest,
        int $totalDues,
        int $payedDues = 0,
        ?string $lastPayedMonth = null
    ): array
    {
        if ($totalDues <= $payedDues){
            return [];
        }

        $capitalMonthlyAmount = $this->calculateNextCapitalAmount(
            $actualDebt,
            $this->calculateNumberOfPendingDues($totalDues, $payedDues)
        );

        $paymentMonth = $lastPayedMonth;
        $duesToPay = [];
        foreach ( range($payedDues + 1, $totalDues ) as $due){
            $interestToPay = ( ($actualDebt * $interest) / 100 );
            $paymentMonth = $this->calculateNextPaymentDate($paymentMonth);
            $duesToPay[] = [
                'number_due' => $due,
                'capital_amount' => $capitalMonthlyAmount,
                'interest' => $interestToPay,
                'total_to_pay' =>  $capitalMonthlyAmount + $interestToPay,
                'payment_month' => $paymentMonth,
            ];
            $paymentMonth = $paymentMonth.'-28';
            $actualDebt -= $capitalMonthlyAmount;
        }

        return $duesToPay;
    }

    /**
     * @param string|null $lastPayedDate should be type 'Y-m-d'
     * @return string|void
     * @throws Exception
     */
    public function calculateNextPaymentDate(?string $lastPayedDate = null): ?string
    {
        if (null != $lastPayedDate){
            $this->isDateFormatValid($lastPayedDate);
        }

        $actualMonth = date($lastPayedDate ?? 'Y-m-d');
        $nextMonth = date("Y-m", strtotime($actualMonth . "+ 1 Month"));

        $nextMonth = $this->formatNextMonth($nextMonth, $actualMonth);

        return (int)substr($actualMonth, -2) < 15 ? substr($actualMonth, 0, 7) : $nextMonth;
    }

    /**
     * @param string $date
     * @return string|void
     * @throws Exception
     */
    public function reverseMonth(string $date): ?string
    {
        if ($this->isDateFormatValid($date)) {

            $dateTime = $this->convertToDateTime($date);
            $dateTime->modify('-1 Month');

            return $dateTime->format('Y-m-t');
        }
        return null;
    }

    /**
     * @param $date
     * @return bool
     * @throws Exception
     */
    private function isDateFormatValid($date){
        $valores = explode('-', $date);
        if(count($valores) == 3 && checkdate($valores[1], $valores[2], $valores[0])){
            return true;
        }

        throw new Exception('Invalid Date Format, expected Y-m-d and received '.$date);
    }

    public function calculateMajorDate(array $dates): string
    {
        array_map([$this, 'convertToDateTime'], $dates);
        /** @var DateTime $majorDate */
        $majorDate = max($dates);

        return $majorDate->format('Y-m-t');
    }

    /**
     * @param $strDate
     * @return DateTime
     * @throws Exception
     */
    private function convertToDateTime($strDate)
    {
        $this->isDateFormatValid($strDate.'-01');

        return new DateTime($strDate-'-01');
    }

    public function calculateHandlingFee($handlingFee, $cardUsers)
    {
        return $handlingFee / $cardUsers;
    }

    public function calculatePaymentMonth(
        $debt,
        $pendingDues,
        $interest,
        $actualDue
    )
    {
        $capitalAmount = $this->calculateNextCapitalAmount($debt, $pendingDues);
        $interestAmount = $this->calculateNextInterestAmount($debt, $interest);

        return [
            'due' => $actualDue,
            'capital_amount' => $capitalAmount,
            'interest_amount' => $interestAmount,
            'total_amount' => $this->calculateNextPaymentAmount($capitalAmount, $interestAmount),
            'mora' => 0,
        ];
    }

    /**
     * @param string $nextMonth
     * @param string $actualMonth
     * @return false|string
     */
    private function formatNextMonth(string $nextMonth, string $actualMonth)
    {
        $monthNext = (int)substr($nextMonth, -2);
        $monthActual = (int)substr($actualMonth, 5, 2);
        if ($monthNext - $monthActual > 1) {
            if (($monthReal = $monthActual + 1) < 10) {
                $monthReal = '0' . $monthReal;
            }
            $nextMonth = date(substr($nextMonth, 0, 5) . $monthReal);
        }
        return $nextMonth;
    }

}

