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

class CreditCalculator
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

    /**
     * Calculate the minimum Capital To Pay Based on Pending Dues
     *
     * @param float $actualDebt
     * @param int $pendingDues
     * @return float
     */
    public function calculateCapitalAmount(float $actualDebt, int $pendingDues): float
    {
        if (0 >= $pendingDues || $actualDebt < 0){
            return 0;
        }

        return $actualDebt / $pendingDues;
    }

    public function calculateInterestAmount($actualDebt, $interest)
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

    public function calculateNextDueToPay(int $totalDues, int $pendingDues): int
    {
        return $totalDues - $pendingDues + 1;
    }

    /**
     * @param int $lastPayedDue
     * @param string $lastMonthPayed
     * @param string $nextPaymentMonth
     * @return DateTime|int
     * @throws Exception
     */
    public function calculateActualDueToPay(int $lastPayedDue, string $lastMonthPayed, string $nextPaymentMonth): int
    {
        $lastPayedDate = strtotime($this->yearMonthToFullDateFormat($lastMonthPayed));
        $nextPayedDate = strtotime($this->yearMonthToFullDateFormat($nextPaymentMonth));

        $actualDue = $lastPayedDue;
        while (($lastPayedDate = strtotime('+1 Month', $lastPayedDate)) <= $nextPayedDate) {
            $actualDue++;
        }

        return $actualDue;
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

        $capitalMonthlyAmount = $this->calculateCapitalAmount(
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
            $actualDebt -= $capitalMonthlyAmount;
        }

        return $duesToPay;
    }

    /**
     * @param string|null $lastPayedMonth should be type 'Y-m-d'
     * @return string|void
     * @throws Exception
     */
    public function calculateNextPaymentDate(?string $lastPayedMonth = null): ?string
    {
        if (null != $lastPayedMonth){
            return $this->increaseMonth($lastPayedMonth);
        }

        $actualMonth = date('Y-m-d');
        $nextMonth = date("Y-m", strtotime($actualMonth . "+ 1 Month"));

        $nextMonth = $this->formatNextMonth($nextMonth, $actualMonth);

        return (int)substr($actualMonth, -2) < 15 ? substr($actualMonth, 0, 7) : $nextMonth;
    }

    /**
     * @param string $date
     * @return string
     * @throws Exception
     */
    public function reverseMonth(string $date): string
    {
        $dateTime = $this->convertToDateTime($date);

        $dateTime->modify('-1 Month');

        return $dateTime->format('Y-m');
    }

    /**
     * @param string $date
     * @return string
     * @throws Exception
     */
    public function increaseMonth(string $date): string
    {
        $dateTime = $this->convertToDateTime($date);

        $dateTime->modify('+1 Month');

        return $dateTime->format('Y-m');
    }

    public function calculateMajorMonth(array $dates): string
    {
        $dates = array_map([$this, 'convertToDateTime'], $dates);
        /** @var DateTime $majorDate */
        $majorDate = max($dates);

        return $majorDate->format('Y-m');
    }

    /**
     * @param string $yearMonth 'Y-m'
     * @param int $day
     * @return string
     * @throws Exception
     */
    private function yearMonthToFullDateFormat(string $yearMonth, int $day = 1): string
    {
        $day = (string)$day < 10 ? '0'.$day : $day;
        $date = sprintf('%s-%s', $yearMonth, $day);

        $this->isDateFormatValid($date);

        return $date;
    }

    /**
     * @param $strDate
     * @return DateTime
     * @throws Exception
     */
    private function convertToDateTime(string $strDate): \DateTime
    {
        return new DateTime($this->yearMonthToFullDateFormat($strDate));
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

//    public function calculateHandlingFee($handlingFee, $cardUsers)
//    {
//        return $handlingFee / $cardUsers;
//    }
//
//    public function calculatePaymentMonth(
//        $debt,
//        $pendingDues,
//        $interest,
//        $actualDue
//    )
//    {
//        $capitalAmount = $this->calculateNextCapitalAmount($debt, $pendingDues);
//        $interestAmount = $this->calculateNextInterestAmount($debt, $interest);
//
//        return [
//            'due' => $actualDue,
//            'capital_amount' => $capitalAmount,
//            'interest_amount' => $interestAmount,
//            'total_amount' => $this->calculateNextPaymentAmount($capitalAmount, $interestAmount),
//            'mora' => 0,
//        ];
//    }

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

