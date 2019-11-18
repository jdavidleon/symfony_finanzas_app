<?php


namespace App\Service;


use App\Exception\InvalidDateTimeFormat;
use DateTime;
use Exception;

class DateHelper
{
    public static function getMinorDate($date1, $date2): string
    {

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
     * @param string $strDate
     * @return DateTime
     * @throws Exception
     */
    public static function convertToDateTime(string $strDate): DateTime
    {
        return new DateTime(self::yearMonthToFullDateFormat($strDate));
    }


    /**
     * @param string $yearMonth 'Y-m'
     * @param int $day
     * @return string
     * @throws InvalidDateTimeFormat
     */
    public static function yearMonthToFullDateFormat(string $yearMonth, int $day = 1): string
    {
        $day = (string)$day < 10 ? '0'.$day : $day;
        $date = sprintf('%s-%s', $yearMonth, $day);

        self::isDateFormatValid($date);

        return $date;
    }

    /**
     * @param $date
     * @return bool
     * @throws InvalidDateTimeFormat
     */
    private static function isDateFormatValid($date): bool
    {
        $valores = explode('-', $date);
        if(count($valores) == 3 && checkdate($valores[1], $valores[2], $valores[0])){
            return true;
        }

        throw new InvalidDateTimeFormat('Invalid Date Format, expected Y-m-d and received '.$date);
    }

}