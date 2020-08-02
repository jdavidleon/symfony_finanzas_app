<?php
/**
 * Created by PhpStorm.
 * User: JLEON
 * Date: 21/12/2018
 * Time: 3:59 PM
 */

namespace App\Service\CreditCard;

use App\Model\Payment\ConsumePaymentResume;
use App\Service\DateHelper;
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

        return round($debt - $payed);
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

        return round($actualDebt / $pendingDues);
    }

    public static function calculateInterestAmount($actualDebt, $interest): float
    {
        return round(( $actualDebt * $interest ) / 100);
    }

    public static function calculateNextPaymentAmount($nextCapitalAmount, $nextInterestAmount): float
    {
        return round($nextCapitalAmount + $nextInterestAmount);
    }
    
    public static function calculateNumberOfPendingDues(int $totalDues, int $payedDues = 0): int
    {
        return $totalDues - $payedDues;
    }

    /**
     * @param int $totalDues
     * @param int $pendingDues
     * @return int|null
     */
    public static function calculateNextDueToPay(int $totalDues, int $pendingDues): ?int
    {
        if (0 >= $pendingDues) {
            return null;
        }
        return $totalDues - $pendingDues + 1;
    }

    /**
     * @param int $totalDues
     * @param int $lastPayedDue
     * @param string $lastMonthPayed
     * @param string $nextPaymentMonth
     * @return DateTime|int
     */
    public static function calculateActualDueToPay(int $totalDues, int $lastPayedDue, string $lastMonthPayed, string $nextPaymentMonth): int
    {
        $lastPayedDate = strtotime(DateHelper::yearMonthToFullDateFormat($lastMonthPayed));
        $nextPayedDate = strtotime(DateHelper::yearMonthToFullDateFormat($nextPaymentMonth));

        $actualDue = $lastPayedDue;
        while (($lastPayedDate = strtotime('+1 Month', $lastPayedDate)) <= $nextPayedDate) {
            $actualDue++;
        }

        return min($totalDues, $actualDue);
    }

    /**
     * Return the list of payment that have to pay till the end of the debt
     * @param float $actualDebt
     * @param float $interest
     * @param int $totalDues
     * @param int $payedDues
     * @param int|null $endDue
     * @param string|null $lastPayedMonth
     * @return ConsumePaymentResume[]
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
        if (($totalDues <= $payedDues) || (null !== $endDue && $payedDues >= $endDue) || 0 == $actualDebt){
            return [];
        }

        $capitalMonthlyAmount = self::calculateCapitalAmount(
            $actualDebt,
            self::calculateNumberOfPendingDues($totalDues, $payedDues)
        );

        $paymentMonth = $lastPayedMonth;
        $duesToPay = [];
        foreach (range($payedDues + 1, $endDue ?? $totalDues ) as $due){
            $interestToPay = self::calculateInterestAmount($actualDebt, $interest);
            $paymentMonth = self::calculateNextPaymentDate($paymentMonth);
            $duesToPay[] = new ConsumePaymentResume(
                $due,
                $actualDebt,
                $capitalMonthlyAmount,
                $interestToPay,
                $paymentMonth
            );
            $actualDebt -= $capitalMonthlyAmount;
        }

        return $duesToPay;
    }

    /**
     * @param string|null $lastPayedMonth should be type 'Y-m'
     * @return string
     * @throws Exception
     */
    public static function calculateNextPaymentDate(?string $lastPayedMonth = null): string
    {
        if (null != $lastPayedMonth){
            return DateHelper::increaseMonth($lastPayedMonth);
        }

        $actualMonth = date('Y-m');
        $nextMonth = DateHelper::increaseMonth($actualMonth);

        return (int)date('d') < 15 ? $actualMonth : $nextMonth;
    }
}

