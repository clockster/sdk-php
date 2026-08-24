<?php

declare(strict_types=1);

namespace Clockster\Generated\Enum;

/**
 * What `status` is allowed to be.
 *
 * Sent in a `$clockster->attendance->record()` body, a filter on `$clockster->attendance->list()`.
 *
 * Constants rather than the cases of an enum, so one goes wherever the string goes:
 * `AttendanceStatus::OUT` is `'out'`, and a static analyser reads the two as one value. Closed on
 * the way in and only there — an answer naming something this class does not is still a string,
 * and still reaches you.
 */
final class AttendanceStatus
{
    public const OUT = 'out';
    public const IN = 'in';
    public const BREAK = 'break';

    /** @return list<'out'|'in'|'break'> */
    public static function values(): array
    {
        return [
            self::OUT,
            self::IN,
            self::BREAK,
        ];
    }
}
