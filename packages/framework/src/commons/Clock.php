<?php
/*
 * Copyright (c) Ouzo contributors, http://ouzoframework.org
 * This file is made available under the MIT License (view the LICENSE file for more information).
 */

namespace Aeatech\Commons;

use DateInterval;
use DatePeriod;
use DateTime;
use DateTimeZone;

/**
 * Class Clock
 */
class Clock
{
    /** @var bool */
    public static $freeze = false;

    /** @var Clock */
    public static $freezeDate;

    /** @var DateTime */
    public $dateTime;

    /** @var DateTimeZone */
    private $dateTimeZone;

    public function __construct(DateTime $dateTime)
    {
        $this->dateTime = clone $dateTime;
        $this->dateTimeZone = $dateTime->getTimezone();
    }

    public static function unfreeze()
    {
        self::$freeze = false;
    }

    /**
     * Freezes time to a specific point or current time if no time is given.
     *
     * @param null $date
     */
    public static function freeze($date = null)
    {
        self::$freezeDate = self::at($date ?? 'now');
        self::$freeze = true;
    }

    /**
     * Returns current time as a string.
     *
     * Example:
     * <code>
     * Clock::freeze('2011-01-02 12:34');
     * $result = Clock::nowAsString('Y-m-d');
     * </code>
     * Result:
     * <code>
     * 2011-01-02
     * </code>
     *
     * @param string $format
     * @return string
     */
    public static function nowAsString($format = null)
    {
        return self::now()->format($format);
    }

    /**
     * Obtains a Clock set to the current time at UTC.
     * @return Clock
     */
    public static function now()
    {
        return self::$freeze
            ? self::$freezeDate
            : self::at('now');
    }

    /**
     * Obtains a Clock set to yesterday at the current time at UTC.
     * @return Clock
     */
    public static function yesterday(): Clock
    {
        return self::now()->minusDays(1);
    }

    public static function tomorrow(): Clock
    {
        return self::now()->plusDays(1);
    }

    /**
     * Obtains a Clock set to to a specific point.
     * @param string $date
     * @param string $timezone Defaults to UTC
     * @return Clock
     */
    public static function at($date, $timezone = 'UTC')
    {
        $dateTime = new DateTime($date, new DateTimeZone($timezone));
        return new Clock($dateTime);
    }

    /**
     * Obtains a Clock set to to a specific point.
     * @param string $date
     * @param string $format
     * @param string $timezone Defaults to UTC
     * @return Clock
     */
    public static function fromFormat(string $date, string $format, string $timezone = 'UTC'): self
    {
        $format = self::prependResetOperatorToFormatIfNotPresent($format);
        $dateTime = \DateTime::createFromFormat($format, $date, new DateTimeZone($timezone));
        return new Clock($dateTime);
    }

    /**
     * Obtains a Clock set to to a specific point using Unix timestamp.
     * @param int $timestamp
     * @return Clock
     */
    public static function fromTimestamp($timestamp)
    {
        $dateTime = new DateTime();
        $dateTime->setTimestamp($timestamp);
        return new Clock($dateTime);
    }

    public function getTimestamp()
    {
        return $this->dateTime->getTimestamp();
    }

    public function getTimezone()
    {
        return $this->dateTime->getTimezone();
    }

    public function format($format = null)
    {
        $format = $format ?: 'Y-m-d H:i:s';
        return $this->dateTime->format($format);
    }

    public function asDateString() {
        return $this->format('Y-m-d');
    }

    public function asDateTimeString()
    {
        return $this->format('Y-m-d H:i:s');
    }

    /**
     * @deprecated
     * @see Clock::asDDMMYYYYDateFormattedString()
     */
    public function asBrazilianDateString(): string
    {
        return $this->format('d/m/Y');
    }

    /**
     * @deprecated
     * @see Clock::asMMYYYFormattedString()
     */
    public function asBrazilianMonthString(): string
    {
        return $this->format('m/Y');
    }

    /**
     * @deprecated
     * @see Clock::asDDMMYYYYDateTimeFormattedString()
     */
    public function asBrazilianDateTimeString(): string
    {
        return $this->format('d/m/Y H:i:s');
    }

    public function asDDMMYYYYDateFormattedString(): string
    {
        return $this->format('d/m/Y');
    }

    public function asMMYYYFormattedString(): string
    {
        return $this->format('m/Y');
    }

    public function asDDMMYYYYDateTimeFormattedString(): string
    {
        return $this->format('d/m/Y H:i:s');
    }

    public function startOfDay()
    {
        return self::at($this->format('Y-m-d 00:00:00'), $this->dateTimeZone->getName());
    }

    public function endOfDay()
    {
        return self::at($this->format('Y-m-d 23:59:59'), $this->dateTimeZone->getName());
    }

    public function startOfMonth()
    {
        return self::at($this->format('Y-m-d 00:00:00') . ' first day of this month', $this->dateTimeZone->getName());
    }

    public function lastDayOfMonth()
    {
        return self::at($this->format('Y-m-t 23:59:59'), $this->dateTimeZone->getName());
    }

    public function startOfPreviousMonth()
    {
        return self::at($this->format('Y-m-d 00:00:00') . ' first day of last month', $this->dateTimeZone->getName());
    }

    public function lastDayOfPreviousMonth()
    {
        return self::at($this->format('Y-m-d 23:59:59') . ' last day of last month', $this->dateTimeZone->getName());
    }

    public function startOfNextMonth()
    {
        return self::at($this->format('Y-m-d 00:00:00') . ' first day of next month', $this->dateTimeZone->getName());
    }

    public function lastDayOfNextMonth()
    {
        return self::at($this->format('Y-m-d 23:59:59') . ' last day of next month', $this->dateTimeZone->getName());
    }

    public function nextDayOfWeek(DayOfWeek $dow): self
    {
        return $this->modify("next {$dow->getName()}");
    }

    public function previousDayOfWeek(DayOfWeek $dow): self
    {
        return $this->modify("previous {$dow->getName()}");
    }

    public function dayOfWeekFromThisWeek(DayOfWeek $dow): self
    {
        return $this->modify("{$dow->getName()} this week");
    }

    public function setTime(int $hour, int $minute, ?int $second = 0): self
    {
        $second = $second ? $second : 0;
        $dateTime = clone $this->dateTime;
        return new Clock($dateTime->setTime($hour, $minute, $second));
    }

    public function startOfHour()
    {
        return self::at($this->format('Y-m-d H:00:00'), $this->dateTimeZone->getName());
    }

    public function endOfHour()
    {
        return self::at($this->format('Y-m-d H:59:59'), $this->dateTimeZone->getName());
    }

    public function startOfMinute()
    {
        return self::at($this->format('Y-m-d H:i:00'), $this->dateTimeZone->getName());
    }

    /**
     * Converts this object to a DateTime
     *
     * @return DateTime
     */
    public function toDateTime()
    {
        return clone $this->dateTime;
    }

    public function toDateTimeImmutable(): \DateTimeImmutable
    {
        return \DateTimeImmutable::createFromMutable($this->dateTime);
    }

    private static function prependResetOperatorToFormatIfNotPresent(string $format): string
    {
        return $format[0] == '!' ? $format : '!' . $format;
    }

    private function modify($interval)
    {
        $dateTime = clone $this->dateTime;
        return new Clock($dateTime->modify($interval));
    }

    private function modifyForWeekdays($interval)
    {
        $dateTime = clone $this->dateTime;
        $dateTimeHours = $dateTime->format('H:i:s');
        return new Clock($dateTime->modify($interval . $dateTimeHours));
    }

    private function modifyWithDstChangeSupport($interval)
    {
        $dateTime = clone $this->dateTime;
        $dateTimeZone = new DateTimeZone('GMT');
        return new Clock($dateTime->setTimezone($dateTimeZone)->modify($interval)->setTimezone($this->dateTimeZone));
    }

    private function modifyMonths($interval)
    {
        $dateTime = clone $this->dateTime;
        $currentDay = $dateTime->format('j');
        $dateTime->modify($interval);
        $endDay = $dateTime->format('j');
        if ($currentDay != $endDay) {
            $dateTime->modify('last day of last month');
        }
        return new Clock($dateTime);
    }

    public function minusDateInterval(\DateInterval $date_interval): self
    {
        return new Clock($this->toDateTime()->sub($date_interval));
    }

    public function minusDays($days)
    {
        return $this->modify("-$days days");
    }

    public function minusWeekdays($weekdays)
    {
        return $this->modifyForWeekdays("-{$weekdays} weekdays");
    }

    public function minusHours($hours)
    {
        return $this->modifyWithDstChangeSupport("-$hours hours");
    }

    public function minusMinutes($minutes)
    {
        return $this->modifyWithDstChangeSupport("-$minutes minutes");
    }

    public function minusMonths($months)
    {
        return $this->modifyMonths("-$months months");
    }

    public function minusSeconds($seconds)
    {
        return $this->modifyWithDstChangeSupport("-$seconds seconds");
    }

    public function minusMilliseconds($milliseconds)
    {
        if (!self::isIntegerLikeValue($milliseconds)) {
            // Backwards compatibility
            return $this->modifyWithDstChangeSupport("-$milliseconds milliseconds");
        }

        $modify_string = $this->decomposeMillisecondsForModify(-$milliseconds);
        return $this->modifyWithDstChangeSupport($modify_string);
    }

    public function minusMicroseconds($microseconds)
    {
        if (!self::isIntegerLikeValue($microseconds)) {
            // Backwards compatibility
            return $this->modifyWithDstChangeSupport("-$microseconds microseconds");
        }

        $modify_string = $this->decomposeMicrosecondsForModify(-$microseconds);
        return $this->modifyWithDstChangeSupport($modify_string);
    }

    public function minusYears($years)
    {
        return $this->modify("-$years years");
    }

    public function plusDateInterval(\DateInterval $date_interval): self
    {
        return new Clock($this->toDateTime()->add($date_interval));
    }

    public function plusDays($days)
    {
        return $this->modify("+$days days");
    }

    public function plusWeekdays($weekdays)
    {
        return $this->modifyForWeekdays("+{$weekdays} weekdays");
    }

    public function plusHours($hours)
    {
        return $this->modifyWithDstChangeSupport("+$hours hours");
    }

    public function plusMinutes($minutes)
    {
        return $this->modifyWithDstChangeSupport("+$minutes minutes");
    }

    public function plusMonths($months)
    {
        return $this->modifyMonths("+$months months");
    }

    public function plusSeconds($seconds)
    {
        return $this->modifyWithDstChangeSupport("+$seconds seconds");
    }

    public function plusMilliseconds($milliseconds)
    {
        if (!self::isIntegerLikeValue($milliseconds)) {
            // Backwards compatibility
            return $this->modifyWithDstChangeSupport("+$milliseconds milliseconds");
        }

        $modify_string = $this->decomposeMillisecondsForModify($milliseconds);
        return $this->modifyWithDstChangeSupport($modify_string);
    }

    public function plusMicroseconds($microseconds)
    {
        if (!self::isIntegerLikeValue($microseconds)) {
            // Backwards compatibility
            return $this->modifyWithDstChangeSupport("+$microseconds microseconds");
        }

        $modify_string = $this->decomposeMicrosecondsForModify($microseconds);
        return $this->modifyWithDstChangeSupport($modify_string);
    }

    public function plusYears($years)
    {
        return $this->modify("+$years years");
    }

    public function isAfter(Clock $other)
    {
        return $this->getTimestamp() > $other->getTimestamp();
    }

    public function isBefore(Clock $other)
    {
        return $this->getTimestamp() < $other->getTimestamp();
    }

    public function isAfterOrEqualTo(Clock $other)
    {
        return $this->getTimestamp() >= $other->getTimestamp();
    }

    public function isBeforeOrEqualTo(Clock $other)
    {
        return $this->getTimestamp() <= $other->getTimestamp();
    }

    public function isInPast(): bool
    {
        return $this->getTimestampInMillis() < self::now()->getTimestampInMillis();
    }

    public function isInFuture(): bool
    {
        return $this->getTimestampInMillis() > self::now()->getTimestampInMillis();
    }

    public function isFirstDayOfMonth(): bool
    {
        return $this->asDateString() === $this->startOfMonth()->asDateString();
    }

    public function isLastDayOfMonth(): bool
    {
        return $this->asDateString() === $this->lastDayOfMonth()->asDateString();
    }

    public function isDayOfWeek(DayOfWeek $dow): bool
    {
        return $this->getDayOfWeek() == $dow;
    }

    public function isWeekEnd(): bool
    {
        return $this->getDayOfWeek()->isWeekEnd();
    }

    public function isWeekDay(): bool
    {
        return $this->getDayOfWeek()->isWeekDay();
    }

    public function deltaTo(Clock $other): TimeDelta
    {
        $dateTimeDiff = $this->dateTime->diff($other->dateTime);

        $delta = new TimeDelta();
        $delta->days = (int)$dateTimeDiff->format('%R%a');

        return $delta;
    }

    public function diffInMillis(Clock $other): int
    {
        return $this->getTimestampInMillis() - $other->getTimestampInMillis();
    }

    public function getDayOfWeek(): DayOfWeek
    {
        return DayOfWeek::fromValue($this->format(DayOfWeek::DATE_FORMAT));
    }

    public function getTimestampInMillis(): int
    {
        $dateTime = $this->toDateTime();
        $timestampInSeconds = (int) $dateTime->format('U');
        $absoluteMillis = (int) $dateTime->format('v');

        return ($timestampInSeconds * 1000) + $absoluteMillis;
    }

    public function diffInMicros(Clock $other): int
    {
        return $this->getTimestampInMicros() - $other->getTimestampInMicros();
    }

    public function getTimestampInMicros(): int
    {
        $dateTime = $this->toDateTime();
        $timestampInSeconds = (int)$dateTime->format('U');
        $absoluteMicros = (int)$dateTime->format('u');

        return ($timestampInSeconds * 1000000) + $absoluteMicros;
    }

    /**
     * @param string|DateTimeZone $timezone
     * @return Clock
     */
    public function withTimezone($timezone)
    {
        $dateTime = clone $this->dateTime;
        $dateTime->setTimezone(is_string($timezone) ? new DateTimeZone($timezone) : $timezone);
        return new Clock($dateTime);
    }

    /**
     * @param Clock $other
     * @param string[] $holidays
     * @return TimeDelta
     */
    public function deltaToInWorkingDays(Clock $other, array $holidays): TimeDelta
    {
        $working_days = [1,2,3,4,5];
        $from = $this->dateTime;
        $to = $other->dateTime;
        $to->modify('+1 day');
        $interval = new DateInterval('P1D');
        $periods = new DatePeriod($from, $interval, $to);
        return $this->calculateWorkingDays($holidays, $periods, $working_days);
    }

    private function calculateWorkingDays(array $holidays, DatePeriod $periods, array $working_days): TimeDelta
    {
        $delta = new TimeDelta();
        $delta->days = 0;
        foreach ($periods as $period) {
            if (!in_array($period->format('N'), $working_days)) continue;
            if (in_array($period->format('Y-m-d'), $holidays)) continue;
            $delta->days++;
        }
        return $delta;
    }

    private static function isIntegerLikeValue($value): bool {
        return \is_numeric($value) && $value == (int)$value;
    }

    /**
     * PHP 7.1 has a bug in which [[DateTime]] instances get inconsistent when
     * big numbers of milliseconds or microseconds are passed to [[modify()]].
     *
     * Example:
     *   $dateTime->modify("+300000 milliseconds");
     *
     * In the above code, we expect that [[$dateTime]] goes 5 minutes ahead.
     * However, PHP completely messes up the [[DateTime]] instance by adding
     * that amount of time in the microseconds portion.
     * [[$dateTime->format('u')]] (microseconds) gives you a number greater
     * than 999,999, which does not make sense. [[getTimestamp()]] is also
     * affected and every comparison function returns wrong results.
     *
     * The same bug can also create [[DateTime]] instances with negative values
     * on microseconds when passing negative numbers to [[modify()]].
     *
     * That's why internal strings such as "+123456 milliseconds" must be
     * converted to "+123 seconds +456 milliseconds" before passing them to
     * [[DateTime::modify()]].
     *
     * Note that the bug does not affect big numbers of seconds such as
     * "+3000000 seconds".
     *
     * @param int $milliseconds a amount of time in milliseconds
     * @return string a string compatible with DateTime's "modify()" method
     */
    private static function decomposeMillisecondsForModify(int $milliseconds): string {
        $modify_milliseconds = $milliseconds % 10**3;
        $modify_seconds = \intdiv($milliseconds, 10**3);

        return \sprintf(
            '%+d seconds %+d milliseconds',
            $modify_seconds,
            $modify_milliseconds
        );
    }

    /**
     * @see [[self::decomposeMillisecondsForModify()]]
     *
     * @param int $microseconds a amount of time in microseconds
     * @return string a string compatible with DateTime's "modify()" method
     */
    private static function decomposeMicrosecondsForModify(int $microseconds): string {
        $modify_microseconds = $microseconds % 10**3;
        $modify_milliseconds = \intdiv($microseconds, 10**3) % 10**3;
        $modify_seconds = \intdiv($microseconds, 10**6);

        return \sprintf(
            '%+d seconds %+d milliseconds %+d microseconds',
            $modify_seconds,
            $modify_milliseconds,
            $modify_microseconds
        );
    }
}

class TimeDelta
{
    public $days;
}
