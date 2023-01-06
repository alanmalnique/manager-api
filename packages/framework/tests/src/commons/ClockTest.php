<?php

namespace Test\Aeatech\Commons;

use Aeatech\Commons\Clock;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class ClockTest extends TestCase
{
    public function testClockAtWithMicroSeconds()
    {
        $clock = Clock::at("2016-06-30 04:00:04.402103");
        Assert::assertEquals("2016-06-30 04:00:04", $clock->asDateTimeString());
    }

    public function testStartOfDay()
    {
        $clock = Clock::at('2018-01-01 12:34:51')->startOfDay();
        Assert::assertEquals('2018-01-01 00:00:00', $clock->format());
    }

    public function testEndOfDay()
    {
        $clock = Clock::at('2018-01-01 12:34:51')->endOfDay();
        Assert::assertEquals('2018-01-01 23:59:59', $clock->format());
    }

    public function testLastDayOfMonth()
    {
        $clock = Clock::at('2019-02-01 12:34:51')->lastDayOfMonth();
        Assert::assertEquals('2019-02-28 23:59:59', $clock->format());
    }

    public function testAsDateString()
    {
        $clock = Clock::now();
        Assert::assertEquals(Clock::nowAsString('Y-m-d'), $clock->asDateString(), 'Trust me, you wont change this format');
    }

    public function testAsDateTimeString()
    {
        $clock = Clock::now();
        Assert::assertEquals(Clock::nowAsString('Y-m-d H:i:s'), $clock->asDateTimeString(), 'Trust me, you wont change this format');
    }

    public function testAsBrazilianDateString()
    {
        $clock = Clock::now();
        Assert::assertEquals(Clock::nowAsString('d/m/Y'), $clock->asBrazilianDateString(), 'Trust me, you wont change this format');
    }

    public function testAsBrazilianDateTimeString()
    {
        $clock = Clock::now();
        Assert::assertEquals(Clock::nowAsString('d/m/Y H:i:s'), $clock->asBrazilianDateTimeString(), 'Trust me, you wont change this format');
    }

    public function testAsDDMMYYYYDateString()
    {
        $clock = Clock::now();
        Assert::assertEquals(Clock::nowAsString('d/m/Y'), $clock->asDDMMYYYYDateFormattedString(), 'Trust me, you wont change this format');
    }

    public function testAsDDMMYYYYDateTimeString()
    {
        $clock = Clock::now();
        Assert::assertEquals(Clock::nowAsString('d/m/Y H:i:s'), $clock->asDDMMYYYYDateTimeFormattedString(), 'Trust me, you wont change this format');
    }

    public function testAsMMYYYYString()
    {
        $clock = Clock::now();
        Assert::assertEquals(Clock::nowAsString('m/Y'), $clock->asMMYYYFormattedString(), 'Trust me, you wont change this format');
    }

    public static function fromFormatTestCases(): \Generator
    {
        yield 'All fields are respected' =>
            ['Y-m-d H:i:s.u', '2018-01-02 00:00:01.0000', Clock::at('2018-01-02 00:00:01.0000')];

        yield 'Missing fields are reset' =>
            ['Y', '2018', Clock::at('2018-01-01 00:00:00.0000')];
    }

    /** @dataProvider fromFormatTestCases */
    public function testFromFormat(string $format, string $date, Clock $expectedResult): void
    {
        $clock = Clock::fromFormat($date, $format);
        Assert::assertEquals($expectedResult, $clock);
    }

    public static function diffTestCases()
    {
        return [
            //                       date_1        date_2              expected_days
            'Single day'             => ['2018-01-01', '2018-01-02',    1,],
            'Single day neg.'        => ['2018-01-01', '2017-12-31',    -1,],
            'Months different'       => ['2018-01-01', '2018-02-01',    31,],
            'Months different neg.'  => ['2018-01-01', '2017-12-01',    -31,],
            'Years different'        => ['2018-01-01', '2019-01-01',    365,],
            'Years different neg.'   => ['2018-01-01', '2017-01-01',   -365,],
        ];
    }

    /** @dataProvider diffTestCases */
    public function testDiff_ShouldReturnTimeDelta($date1, $date2, $expected_days)
    {
        $clock1 = Clock::at($date1);
        $clock2 = Clock::at($date2);

        $diff = $clock1->deltaTo($clock2);

        Assert::assertEquals($expected_days, $diff->days);
    }

    public static function diffInMillisTestCases(): array
    {
        return [
            //                     date_time_a                  date_time_b                expected_ms
            'One second'       => ['2018-01-01 00:00:01',      '2018-01-01 00:00:00',             1000],
            'One second neg'   => ['2018-01-01 00:00:00',      '2018-01-01 00:00:01',            -1000],
            'Several millis'   => ['2018-01-01 00:00:00.092',  '2018-01-01 00:00:00.050',           42],
            'One milli'        => ['2018-01-01 00:00:00.002',  '2018-01-01 00:00:00.001',            1],
            'Zero millis'      => ['2018-01-01 00:00:00.502',  '2018-01-01 00:00:00.502',            0],
            '0.9 milli'        => ['2018-01-01 00:00:00.0009', '2018-01-01 00:00:00.0000',           0],
            '0.9 milli neg'    => ['2018-01-01 00:00:00.0000', '2018-01-01 00:00:00.0009',           0],
            '0.4 millis'       => ['2018-01-01 00:00:00.0004', '2018-01-01 00:00:00.0000',           0],
            '0.4 millis neg'   => ['2018-01-01 00:00:00.0000', '2018-01-01 00:00:00.0004',           0],
            'Across days'      => ['2018-01-02 00:00:01.0000', '2018-01-01 23:59:59.0004',        2000],
            'Across days neg'  => ['2018-01-01 23:59:59.0004', '2018-01-02 00:00:01.0000',       -2000],
            'One day'          => ['2018-01-02 12:00:00.0000', '2018-01-01 12:00:00.0000',    86400000],
        ];
    }

    /** @dataProvider diffInMillisTestCases */
    public function testDiffInMillis(string $dateTimeA, string $dateTimeB, int $expectedMillis): void
    {
        $a = Clock::at($dateTimeA);
        $b = Clock::at($dateTimeB);

        Assert::assertEquals($expectedMillis, $a->diffInMillis($b));
    }

    public function testDiffInMillisWithClocksInDifferentTimezones(): void
    {
        $a = Clock::at('2018-01-01 03:00:01', 'UTC');
        $b = Clock::at('2018-01-01 00:00:00', '-0300');

        Assert::assertEquals(1000, $a->diffInMillis($b));
    }

    public static function getTimestampInMillisTestCases(): array
    {
        return [
            //                      datetime                   expected_timestamp
            'One milli'         => ['1970-01-01 00:00:00.001',                  1],
            'One milli neg'     => ['1969-12-31 23:59:59.999',                 -1],
            '1.999 seconds neg' => ['1969-12-31 23:59:58.001',              -1999],
            'Many years'        => ['2019-06-04 13:59:27.654',      1559656767654],
        ];
    }

    /** @dataProvider getTimestampInMillisTestCases */
    public function testGetTimestampInMillis(string $datetime, int $expectedTimestamp)
    {
        Assert::assertEquals($expectedTimestamp, Clock::at($datetime)->getTimestampInMillis());
    }

    public function testToDateTimeImmutable()
    {
        $subject = Clock::at('2019-06-04 13:59:27.654', 'America/Sao_Paulo');
        Assert::assertEquals(new \DateTimeImmutable('2019-06-04 13:59:27.654'), $subject->toDateTimeImmutable());
    }

    public function testFreeze_ShouldFreezeWithMicrosecondsPrecision()
    {
        Clock::freeze('2018-01-01 12:00:00.424242');
        Assert::assertEquals('2018-01-01 12:00:00.424242', Clock::nowAsString('Y-m-d H:i:s.u'));
    }

    public function dataProviderToTestSetTime()
    {
        return [
        //   date time before       H   i    s     date time after
            ['2020-03-04 12:31:01', 21, 13,  10,   '2020-03-04 21:13:10'],
            ['2021-06-19 22:01:40', 1,  4,   9,    '2021-06-19 01:04:09'],
            ['2021-02-01 14:40:59', 10, 20,  null, '2021-02-01 10:20:00'],
            ['2021-04-10 16:54:08', 0,  0,   0,    '2021-04-10 00:00:00'],
            ['2021-07-21 00:00:00', 23, 59,  59,   '2021-07-21 23:59:59'],
            ['2021-06-11 11:03:02', 15, 20,  50,   '2021-06-11 15:20:50'],
            ['2021-06-11 11:03:02', 15, 70,  50,   '2021-06-11 16:10:50'],
            ['2021-06-11 11:03:02', 15, -10, 50,   '2021-06-11 14:50:50'],
        ];
    }

    /** @dataProvider dataProviderToTestSetTime */
    public function testSetTime(
        string $originalDateTime,
        int $hour,
        int $minute,
        ?int $second,
        string $expectedDateTime
    )
    {
        $clock = Clock::at($originalDateTime);
        $modifiedClock = $clock->setTime($hour, $minute, $second);
        Assert::assertNotSame($modifiedClock, $clock);
        Assert::assertEquals($expectedDateTime, $modifiedClock->format());
    }

    public function testStartOfHour()
    {
        $clock = Clock::at('2018-01-01 12:31:01')->startOfHour();
        Assert::assertEquals('2018-01-01 12:00:00', $clock->format());
    }

    public function testEndOfHour()
    {
        $clock = Clock::at('2018-01-01 18:31:01')->endOfHour();
        Assert::assertEquals('2018-01-01 18:59:59', $clock->format());
    }

    public function dataProviderToTestDeltaToInWorkingDays()
    {
        return [
            'Should not consider weekends' => [
                'start_date' => '2019-03-22',
                'end_date' => '2019-03-26',
                'expected_diff' => 3,
            ],
            'Should not consider holidays' => [
                'start_date' => '2018-12-31',
                'end_date' => '2019-01-02',
                'expected_diff' => 1,
            ],
        ];
    }

    /** @dataProvider dataProviderToTestDeltaToInWorkingDays */
    public function testDeltaToInWorkingDays(string $start_date, string $end_date, int $expected_diff)
    {
        $brazilian_holidays = ['2019-01-01', '2018-12-31'];
        $delta = $clock = Clock::at($start_date)->deltaToInWorkingDays(Clock::at($end_date), $brazilian_holidays);
        Assert::assertEquals($expected_diff, $delta->days);
    }

    public function testMinusDateInterval_ShouldSubtractDateInterval()
    {
        $date_interval = \DateInterval::createFromDateString('15 minutes');
        $clock_a = Clock::at('2019-01-01 20:00:00');
        $clock_b = $clock_a->minusDateInterval($date_interval);
        Assert::assertEquals('2019-01-01 20:00:00', $clock_a->format());
        Assert::assertEquals('2019-01-01 19:45:00', $clock_b->format());
    }

    public function testPlusDateInterval_ShouldAddDateInterval()
    {
        $date_interval = \DateInterval::createFromDateString('15 minutes');
        $clock_a = Clock::at('2019-01-01 20:00:00');
        $clock_b = $clock_a->plusDateInterval($date_interval);
        Assert::assertEquals('2019-01-01 20:00:00', $clock_a->format());
        Assert::assertEquals('2019-01-01 20:15:00', $clock_b->format());
    }

    public static function provideMinusMillisecondsCases(): iterable
    {
        yield [ '2019-01-01 20:00:00.002001',    901,       '2019-01-01 19:59:59.101001' ];
        yield [ '2021-12-06 21:41:00.173458',    301234,    '2021-12-06 21:35:58.939458' ]; // PHP 7.1 issue
    }

    /** @dataProvider provideMinusMillisecondsCases */
    public function testMinusMilliseconds_ShouldSubtractMilliseconds(string $base_datetime, int $milliseconds, string $expected_modified_datetime)
    {
        $clock_a = Clock::fromFormat($base_datetime, 'Y-m-d H:i:s.u');
        $clock_b = $clock_a->minusMilliseconds($milliseconds);
        Assert::assertEquals($base_datetime, $clock_a->format('Y-m-d H:i:s.u'));
        Assert::assertEquals($expected_modified_datetime, $clock_b->format('Y-m-d H:i:s.u'));
    }

    public static function providePlusMillisecondsCases(): iterable
    {
        yield [ '2019-01-01 20:00:00.002001',    901,       '2019-01-01 20:00:00.903001' ];
        yield [ '2021-12-06 21:41:00.173458',    301234,    '2021-12-06 21:46:01.407458' ]; // PHP 7.1 issue
    }

    /** @dataProvider providePlusMillisecondsCases */
    public function testPlusMilliseconds_ShouldAddMilliseconds(string $base_datetime, int $milliseconds, string $expected_modified_datetime)
    {
        $clock_a = Clock::fromFormat($base_datetime, 'Y-m-d H:i:s.u');
        $clock_b = $clock_a->plusMilliseconds($milliseconds);
        Assert::assertEquals($base_datetime, $clock_a->format('Y-m-d H:i:s.u'));
        Assert::assertEquals($expected_modified_datetime, $clock_b->format('Y-m-d H:i:s.u'));
    }

    public function testTomorrow() {
        $frozen_at = '2019-09-09 15:26:01';
        Clock::freeze($frozen_at);
        $subject = Clock::tomorrow();

        Assert::assertEquals('2019-09-10 15:26:01', $subject->asDateTimeString());
    }

    public function testYesterday() {
        $frozen_at = '2019-09-09 15:26:01';
        Clock::freeze($frozen_at);
        $subject = Clock::yesterday();

        Assert::assertEquals('2019-09-08 15:26:01', $subject->asDateTimeString());
    }

    public function testStartOfNextMonth() {
	    $frozen_at = '2019-09-09 15:26:01';
	    Clock::freeze($frozen_at);
	    $subject = Clock::now()->startOfNextMonth();

	    Assert::assertEquals('2019-10-01 00:00:00', $subject->asDateTimeString());
    }

    public function testLastDayOfNextMonth() {
	    $frozen_at = '2019-09-09 15:26:01';
	    Clock::freeze($frozen_at);
	    $subject = Clock::now()->lastDayOfNextMonth();

	    Assert::assertEquals('2019-10-31 23:59:59', $subject->asDateTimeString());
    }

    public function testIsInPast() {
    	$frozen_at = '2019-09-10 15:26:01';
    	Clock::freeze($frozen_at);
    	$subject = Clock::at('2019-09-10 15:00:01');

	    Assert::assertTrue($subject->isInPast(), 'Trust me, it is in past');
    }

    public function testIsInPast_ShouldReturnFalse_DateIsInPresent() {
    	$frozen_at = '2019-09-10 15:26:01';
    	Clock::freeze($frozen_at);
    	$subject = Clock::at('2019-09-10 15:26:01');

	    Assert::assertFalse($subject->isInPast(), 'Trust me, it is not in past');
    }

    public function testIsInFuture() {
    	$frozen_at = '2019-09-10 15:26:01';
    	Clock::freeze($frozen_at);
    	$subject = Clock::at('2019-09-10 16:00:01');

	    Assert::assertTrue($subject->isInFuture(), 'Trust me, it is in future');
    }

    public function testIsInFuture_ShouldReturnFalse_DateIsInPresent() {
    	$frozen_at = '2019-09-10 15:26:01';
    	Clock::freeze($frozen_at);
    	$subject = Clock::at('2019-09-10 15:26:01');

	    Assert::assertFalse($subject->isInFuture(), 'Trust me, it is not in future');
    }

    public function testIsFirstDayOfMonth() {
    	$subject = Clock::at('2019-09-01 15:26:01');
	    Assert::assertTrue($subject->isFirstDayOfMonth(), 'Trust me, it is the first day of the month');
    }

    public function testIsFirstDayOfMonth_ShouldReturnFalse_DateIsInAnotherDay() {
    	$subject = Clock::at('2019-09-10 15:26:01');

	    Assert::assertFalse($subject->isFirstDayOfMonth(), 'Trust me, it is not first day of the month');
    }

    public function testIsLastDayOfMonth() {
    	$subject = Clock::at('2019-09-30 15:26:01');
	    Assert::assertTrue($subject->isLastDayOfMonth(), 'Trust me, it is the last day of the month');
    }

    public function testIsLastDayOfMonth_ShouldReturnFalse_DateIsInAnotherDay() {
    	$subject = Clock::at('2019-09-10 15:26:01');

	    Assert::assertFalse($subject->isLastDayOfMonth(), 'Trust me, it is not last day of the month');
    }

    public static function providePlusMicrosecondsCases(): iterable
    {
        yield [ '2019-01-01 20:00:00.000000',    2001,      '2019-01-01 20:00:00.002001' ];
        yield [ '2021-12-06 21:41:00.173458',    312345679, '2021-12-06 21:46:12.519137' ]; // PHP 7.1 issue
    }

    /** @dataProvider providePlusMicrosecondsCases */
    public function testPlusMicroseconds_ShouldAddMicroseconds(string $base_datetime, int $microseconds, string $expected_modified_datetime)
    {
        $clock_a = Clock::fromFormat($base_datetime, 'Y-m-d H:i:s.u');
        $clock_b = $clock_a->plusMicroseconds($microseconds);
        Assert::assertEquals($base_datetime, $clock_a->format('Y-m-d H:i:s.u'));
        Assert::assertEquals($expected_modified_datetime, $clock_b->format('Y-m-d H:i:s.u'));
    }

    public static function diffInMicrosTestCases(): iterable
    {
        return [
            //                     date_time_a                   date_time_b                     expected_ms
            'Zero micros'      => ['2018-01-01 00:00:00.123456', '2018-01-01 00:00:00.123456',            0],
            'One micro'        => ['2018-01-01 00:00:00.000001', '2018-01-01 00:00:00.000000',            1],
            'One micro neg'    => ['2018-01-01 00:00:00.000000', '2018-01-01 00:00:00.000001',           -1],
            '10 micro'        =>  ['2018-01-01 00:00:00.000011', '2018-01-01 00:00:00.000001',           10],
            '10 micro neg'    =>  ['2018-01-01 00:00:00.000001', '2018-01-01 00:00:00.000011',          -10],
            '0.4 millis'       => ['2018-01-01 00:00:00.0004',   '2018-01-01 00:00:00.0000',            400],
            '0.4 millis neg'   => ['2018-01-01 00:00:00.0000',   '2018-01-01 00:00:00.0004',           -400],
            'Half milli'       => ['2018-01-01 00:00:00.0005',   '2018-01-01 00:00:00.0000',            500],
            'Half milli neg'   => ['2018-01-01 00:00:00.0000',   '2018-01-01 00:00:00.0005',           -500],
            'One milli'        => ['2018-01-01 00:00:00.002',    '2018-01-01 00:00:00.001',            1000],
            'Several millis'   => ['2018-01-01 00:00:00.092',    '2018-01-01 00:00:00.050',           42000],
            'One second'       => ['2018-01-01 00:00:01',        '2018-01-01 00:00:00',             1000000],
            'One second neg'   => ['2018-01-01 00:00:00',        '2018-01-01 00:00:01',            -1000000],
            'Across days'      => ['2018-01-02 00:00:01.0000',   '2018-01-01 23:59:59.0004',        1999600],
            'Across days neg'  => ['2018-01-01 23:59:59.0004',   '2018-01-02 00:00:01.0000',       -1999600],
            'One day'          => ['2018-01-02 12:00:00.0000',   '2018-01-01 12:00:00.0000',    86400000000],
        ];
    }

    /** @dataProvider diffInMicrosTestCases */
    public function testDiffInMicros(string $dateTimeBase, string $dateTimeTarget, int $expectedMicros): void {
        Assert::assertEquals(
            $expectedMicros,
            Clock::at($dateTimeBase)
                ->diffInMicros(
                    Clock::at($dateTimeTarget)
                )
        );
    }

    public function testDiffInMicrosWithClocksInDifferentTimezones(): void {
        Assert::assertEquals(
            1000000,
            Clock::at('2018-01-01 03:00:01', 'UTC')
                ->diffInMicros(
                    Clock::at('2018-01-01 00:00:00', '-0300')
                )
        );
    }

    public static function provideMinusMicrosecondsCases(): iterable
    {
        yield [ '2019-01-01 20:00:00.002001',    2001,      '2019-01-01 20:00:00.000000' ];
        yield [ '2021-12-06 21:41:00.173458',    312345679, '2021-12-06 21:35:47.827779' ]; // PHP 7.1 issue
    }

    /** @dataProvider provideMinusMicrosecondsCases */
    public function testMinusMicroseconds_ShouldSubtractMicroseconds(string $base_datetime, int $microseconds, string $expected_modified_datetime)
    {
        $clock_a = Clock::fromFormat($base_datetime, 'Y-m-d H:i:s.u');
        $clock_b = $clock_a->minusMicroseconds($microseconds);
        Assert::assertEquals($base_datetime, $clock_a->format('Y-m-d H:i:s.u'));
        Assert::assertEquals($expected_modified_datetime, $clock_b->format('Y-m-d H:i:s.u'));
    }

    public function testStartOfMinute()
    {
        $clock = Clock::at('2018-01-01 12:34:51')->startOfMinute();
        Assert::assertEquals('2018-01-01 12:34:00', $clock->format());
    }

    public function getCasesToTestPlusWeekdays(): iterable
    {
        yield [$days_to_add = 1, $monday = '2019-07-01 12:00:00', $tuesday = '2019-07-02 12:00:00'];
        yield [$days_to_add = 2, $monday = '2019-07-01 12:00:00', $wednesday = '2019-07-03 12:00:00'];
        yield [$days_to_add = 3, $monday = '2019-07-01 12:00:00', $thursday = '2019-07-04 12:00:00'];
        yield [$days_to_add = 4, $monday = '2019-07-01 12:00:00', $friday = '2019-07-05 12:00:00'];
        yield [$days_to_add = 5, $monday = '2019-07-01 12:00:00', $following_monday = '2019-07-08 12:00:00'];
        yield [$days_to_add = 1, $friday = '2019-07-05 12:00:00', $following_monday = '2019-07-08 12:00:00'];
        yield [$days_to_add = 1, $saturday = '2019-07-06 12:00:00', $following_monday = '2019-07-08 12:00:00'];
        yield [$days_to_add = 1, $sunday = '2019-07-07 12:00:00', $following_monday = '2019-07-08 12:00:00'];
        yield [$days_to_add = 2, $sunday = '2019-07-07 12:00:00', $following_tuesday = '2019-07-09 12:00:00'];
    }

    /**
     * @dataProvider getCasesToTestPlusWeekdays
     */
    public function testPlusWeekdays(int $weekdays_to_add, string $actual_day, string $expected_weekday)
    {
        $clock = Clock::at($actual_day)->plusWeekdays($weekdays_to_add);
        Assert::assertEquals($expected_weekday, $clock->asDateTimeString());
    }

    public function getCasesToTestMinusWeekdays(): iterable
    {
        yield [$days_to_go_back = 1, $tuesday = '2019-07-02 12:00:00', $monday = '2019-07-01 12:00:00'];
        yield [$days_to_go_back = 2, $wednesday = '2019-07-03 12:00:00', $monday = '2019-07-01 12:00:00'];
        yield [$days_to_go_back = 3, $thursday = '2019-07-04 12:00:00', $monday = '2019-07-01 12:00:00'];
        yield [$days_to_go_back = 4, $friday = '2019-07-05 12:00:00', $monday = '2019-07-01 12:00:00'];
        yield [$days_to_go_back = 5, $monday = '2019-07-08 12:00:00', $past_monday = '2019-07-01 12:00:00'];
        yield [$days_to_go_back = 1, $monday = '2019-07-08 12:00:00', $past_friday = '2019-07-05 12:00:00'];
        yield [$days_to_go_back = 1, $saturday = '2019-07-06 12:00:00', $past_friday = '2019-07-05 12:00:00'];
        yield [$days_to_go_back = 1, $sunday = '2019-07-07 12:00:00', $past_friday = '2019-07-05 12:00:00'];
        yield [$days_to_go_back = 2, $sunday = '2019-07-07 12:00:00', $past_thursday = '2019-07-04 12:00:00'];
    }

    /**
     * @dataProvider getCasesToTestMinusWeekdays
     */
    public function testMinusWeekdays(int $weekdays_to_go_back, string $actual_day, string $expected_weekday)
    {
        $clock = Clock::at($actual_day)->minusWeekdays($weekdays_to_go_back);
        Assert::assertEquals($expected_weekday, $clock->asDateTimeString());
    }

    public function testPlusWeekdaysAndPlusDaysFunctions_ShouldHaveDifferentBehaviorsOnWeekends()
    {
        $friday = '2019-07-05 12:00:00';
        $clock = Clock::at($friday);

        $clock_plus_days = $clock->plusDays(1)->asDateTimeString();
        $clock_plus_weekdays = $clock->plusWeekdays(1)->asDateTimeString();

        Assert::assertEquals($saturday = '2019-07-06 12:00:00', $clock_plus_days);
        Assert::assertEquals($following_monday = '2019-07-08 12:00:00', $clock_plus_weekdays);
    }

    public function testMinusWeekdaysAndMinusDaysFunctions_ShouldHaveDifferentBehaviorsOnWeekends()
    {
        $monday = '2019-07-08 12:00:00';
        $clock = Clock::at($monday);

        $clock_plus_days = $clock->minusDays(1)->asDateTimeString();
        $clock_plus_weekdays = $clock->minusWeekdays(1)->asDateTimeString();

        Assert::assertEquals($sunday = '2019-07-07 12:00:00', $clock_plus_days);
        Assert::assertEquals($past_friday = '2019-07-05 12:00:00', $clock_plus_weekdays);
    }

    public function testStartOfPreviousMonth_ShouldReturnStartOfDayTime()
    {
        $clock = Clock::at('2020-11-10 15:12:57');
        $start_of_previous_month = $clock->startOfPreviousMonth()->asDateTimeString();

        Assert::assertEquals('2020-10-01 00:00:00', $start_of_previous_month);
    }
}
