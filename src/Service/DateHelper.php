<?php


namespace App\Service;


use App\Exception\InvalidDateTimeFormat;
use DateTime;
use Exception;

class DateHelper
{
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

    /**
     * @param string $first
     * @param string $second
     * @return int
     * @throws Exception
     */
    public static function calculateDatesDifferenceMonths(string $first, string $second): int
    {
        $start = self::convertToDateTime($first);
        $end = self::convertToDateTime($second);
        $diff = $start->diff($end);

        return (int)($diff->format('%y') * 12) + (int)($diff->format('%m'));
    }

//    public static function isTheMajorDate(string $dateToEvaluate, array $comparisonDateList)
//    {
//        if (in_array($dateToEvaluate, $comparisonDateList)) {
//            return true;
//        }
//
//        array_push($comparisonDateList, $dateToEvaluate);
//        return self::calculateMajorMonth($comparisonDateList) == $dateToEvaluate;
//    }

    /**
     * @param array $dates ['Y-m']
     * @return string
     */
    public static function calculateMajorMonth(array $dates): string
    {
        $dates = array_map([self::class, 'convertToDateTime'], $dates);
        /** @var DateTime $majorDate */
        $majorDate = max($dates);

        return $majorDate->format('Y-m');
    }

    /**
     * @param string $strDate Expected format 'Y-m'
     * @return DateTime
     * @throws Exception
     */
    public static function convertToDateTime(string $strDate): DateTime
    {
        return new DateTime(self::yearMonthToFullDateFormat($strDate));
    }


    /**
     * Convertimos un string con formato 'Y-m' en un 'Y-m-d'
     *
     * @param string $yearMonth 'Y-m'
     * @param int $day
     * @return string
     * @throws InvalidDateTimeFormat
     */
    public static function yearMonthToFullDateFormat(string $yearMonth, int $day = 1): string
    {
        $day = (string)$day < 10 ? '0' . $day : $day;
        $date = sprintf('%s-%s', $yearMonth, $day);

        self::isDateFormatValid($date);

        return $date;
    }

    /**
     * Este método verifica que una fecha este dada en el formato 'Y-m-d'
     *
     * @param string $date
     * @return bool
     */
    private static function isDateFormatValid(string $date): bool
    {
        $valores = explode('-', $date);
        if (count($valores) == 3 && checkdate($valores[1], $valores[2], $valores[0])) {
            return true;
        }

        throw new InvalidDateTimeFormat('Invalid Date Format, expected Y-m-d and received ' . $date);
    }

}