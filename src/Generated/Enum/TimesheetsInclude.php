<?php

declare(strict_types=1);

namespace Clockster\Generated\Enum;

/**
 * What `include` is allowed to be.
 *
 * Sent in a filter on $clockster->timesheets->list().
 *
 * Constants rather than the cases of an enum, so one goes wherever the string goes:
 * `TimesheetsInclude::ACTUAL` is `'actual'`, and a static analyser reads the two as one value.
 * Closed on the way in and only there — an answer naming something this class does not is still a
 * string, and still reaches you.
 */
final class TimesheetsInclude
{
    public const ACTUAL = 'actual';
    public const VARIANCE = 'variance';
    public const USER = 'user';
    public const LOCATION = 'location';
    public const DEPARTMENT = 'department';
    public const POSITION = 'position';

    /** @return list<'actual'|'variance'|'user'|'location'|'department'|'position'> */
    public static function values(): array
    {
        return [
            self::ACTUAL,
            self::VARIANCE,
            self::USER,
            self::LOCATION,
            self::DEPARTMENT,
            self::POSITION,
        ];
    }
}
