<?php


namespace App\Tests\Service;


use App\Service\DateHelper;
use Exception;
use PHPUnit\Framework\TestCase;

class DateHelperTest extends TestCase
{

    /**
     * @param string|null $date
     * @param string $expected
     * @param string $message
     * @throws Exception
     *
     * @dataProvider getReverseMonthCases
     */
    public function testReverseMonth(?string $date, ?string $expected, string $message)
    {
        $month = DateHelper::reverseMonth($date);

        self::assertSame($expected, $month, $message);
    }

    public function getReverseMonthCases()
    {
        return [
            ['2019-04', '2019-03', 'reverse month'],
            ['2019-12', '2019-11', 'December'],
            ['2019-01', '2018-12', 'January'],
        ];
    }

    /**
     * @param string|null $date
     * @param string $expected
     * @param string $message
     * @throws Exception
     *
     * @dataProvider getIncreaseMonthCases
     */
    public function testIncreaseMonth(?string $date, ?string $expected, string $message)
    {
        $month = DateHelper::increaseMonth($date);

        self::assertSame($expected, $month, $message);
    }

    public function getIncreaseMonthCases()
    {
        return [
            ['2019-05', '2019-06', 'increase month'],
            ['2019-12', '2020-01', 'December'],
            ['2019-01', '2019-02', 'January'],
        ];
    }

    /**
     * @param array $dates
     * @param string $expected
     * @param string $message
     *
     * @dataProvider getCalculateMajorMonthCases
     */
    public function testCalculateMajorMonth(array $dates, string $expected, string $message)
    {
        $month = DateHelper::calculateMajorMonth($dates);

        self::assertSame($expected, $month, $message);
    }

    public function getCalculateMajorMonthCases()
    {
        return [
            [
                ['2019-04', '2020-04', '2019-11'],
                '2020-04',
                'Major Month'
            ],
            [
                ['2019-11', '2019-12', '2020-01'],
                '2020-01',
                'Major Month'
            ],
        ];
    }
}