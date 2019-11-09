<?php
/**
 * Created by PhpStorm.
 * User: JLEON
 * Date: 21/12/2018
 * Time: 3:59 PM
 */

namespace App\Service\CreditCard;

use App\Exception\InvalidDateTimeFormat;
use DateTime;
use Exception;

class CreditCalculator
{
    /**
     * @param float $payed
     * @param float $debt
     * @return float
     */
    public static function calculateActualCreditCardConsumeDebt(float $debt, float $payed): float
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
    public static function calculateCapitalAmount(float $actualDebt, int $pendingDues): float
    {
        if (0 >= $pendingDues || $actualDebt < 0){
            return 0;
        }

        return $actualDebt / $pendingDues;
    }

    public static function calculateInterestAmount($actualDebt, $interest)
    {
        return ( $actualDebt * $interest ) / 100;
    }

    public static function calculateNextPaymentAmount($nextCapitalAmount, $nextInterestAmount)
    {
        return $nextCapitalAmount + $nextInterestAmount;
    }
    
    public static function calculateNumberOfPendingDues(int $totalDues, int $payedDues = 0): int
    {
        return $totalDues - $payedDues;
    }

    public static function calculateNextDueToPay(int $totalDues, int $pendingDues): int
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
    public static function calculateActualDueToPay(int $lastPayedDue, string $lastMonthPayed, string $nextPaymentMonth): int
    {
        $lastPayedDate = strtotime(self::yearMonthToFullDateFormat($lastMonthPayed));
        $nextPayedDate = strtotime(self::yearMonthToFullDateFormat($nextPaymentMonth));

        $actualDue = $lastPayedDue;
        while (($lastPayedDate = strtotime('+1 Month', $lastPayedDate)) <= $nextPayedDate) {
            $actualDue++;
        }

        return $actualDue;
    }

    /**
     * Return the list of payment that have to pay till the end of the debt
     * @param float $actualDebt
     * @param float $interest
     * @param int $totalDues
     * @param int $payedDues
     * @param int|null $endDue
     * @param string|null $lastPayedMonth
     * @return array
     * @throws Exception
     */
    public static function calculatePendingPaymentsResume(
        float $actualDebt,
        float $interest,
        int $totalDues,
        int $payedDues = 0,
        ?int $endDue = null,
        ?string $lastPayedMonth = null
    ): array
    {
        if ($totalDues <= $payedDues){
            return [];
        }

        $capitalMonthlyAmount = self::calculateCapitalAmount(
            $actualDebt,
            self::calculateNumberOfPendingDues($totalDues, $payedDues)
        );

        if (null == $endDue) {
            $endDue = $totalDues;
        }

        $paymentMonth = $lastPayedMonth;
        $duesToPay = [];
        foreach ( range($payedDues + 1, $endDue ) as $due){
            $interestToPay = ( ($actualDebt * $interest) / 100 );
            $paymentMonth = self::calculateNextPaymentDate($paymentMonth);
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
    public static function calculateNextPaymentDate(?string $lastPayedMonth = null): ?string
    {
        if (null != $lastPayedMonth){
            return self::increaseMonth($lastPayedMonth);
        }

        $actualMonth = date('Y-m-d');
        $nextMonth = date("Y-m", strtotime($actualMonth . "+ 1 Month"));

        $nextMonth = self::formatNextMonth($nextMonth, $actualMonth);

        return (int)substr($actualMonth, -2) < 15 ? substr($actualMonth, 0, 7) : $nextMonth;
    }

    /**
     * @param string $date
     * @return string
     * @throws Exception
     */
    public static function reverseMonth(string $date): string
    {
        $dateTime = self::convertToDateTime($date);

        $dateTime->modify('-1 Month');

        return $dateTime->format('Y-m');
    }

    /**
     * @param string $date
     * @return string
     * @throws Exception
     */
    public static function increaseMonth(string $date): string
    {
        $dateTime = self::convertToDateTime($date);

        $dateTime->modify('+1 Month');

        return $dateTime->format('Y-m');
    }

    public static function calculateMajorMonth(array $dates): string
    {
        $dates = array_map([self::class, 'convertToDateTime'], $dates);
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
    private static function yearMonthToFullDateFormat(string $yearMonth, int $day = 1): string
    {
        $day = (string)$day < 10 ? '0'.$day : $day;
        $date = sprintf('%s-%s', $yearMonth, $day);

        self::isDateFormatValid($date);

        return $date;
    }

    /**
     * @param $strDate
     * @return DateTime
     * @throws Exception
     */
    private static function convertToDateTime(string $strDate): \DateTime
    {
        return new DateTime(self::yearMonthToFullDateFormat($strDate));
    }

    /**
     * @param $date
     * @return bool
     * @throws InvalidDateTimeFormat
     */
    private static function isDateFormatValid($date){
        $valores = explode('-', $date);
        if(count($valores) == 3 && checkdate($valores[1], $valores[2], $valores[0])){
            return true;
        }

        throw new InvalidDateTimeFormat('Invalid Date Format, expected Y-m-d and received '.$date);
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
    private static function formatNextMonth(string $nextMonth, string $actualMonth)
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

