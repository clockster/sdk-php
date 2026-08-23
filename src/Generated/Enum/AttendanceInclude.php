<?php

declare(strict_types=1);

namespace Clockster\Generated\Enum;

/**
 * What `include` is allowed to be.
 *
 * Sent in a filter on $clockster->attendance->list().
 *
 * Constants rather than the cases of an enum, so one goes wherever the string goes:
 * `AttendanceInclude::USER` is `'user'`, and a static analyser reads the two as one value. Closed
 * on the way in and only there — an answer naming something this class does not is still a
 * string, and still reaches you.
 */
final class AttendanceInclude
{
    public const USER = 'user';
    public const LOCATION = 'location';
    public const ATTACHMENTS = 'attachments';

    /** @return list<'user'|'location'|'attachments'> */
    public static function values(): array
    {
        return [
            self::USER,
            self::LOCATION,
            self::ATTACHMENTS,
        ];
    }
}
