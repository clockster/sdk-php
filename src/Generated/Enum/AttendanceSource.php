<?php

declare(strict_types=1);

namespace Clockster\Generated\Enum;

/**
 * What `source` is allowed to be.
 *
 * Sent in a filter on $clockster->attendance->list().
 *
 * Constants rather than the cases of an enum, so one goes wherever the string goes:
 * `AttendanceSource::DEVICE` is `'device'`, and a static analyser reads the two as one value.
 * Closed on the way in and only there — an answer naming something this class does not is still a
 * string, and still reaches you.
 */
final class AttendanceSource
{
    public const DEVICE = 'device';
    public const MOBILE = 'mobile';
    public const FRONTEND = 'frontend';
    public const API = 'api';
    public const SYSTEM = 'system';

    /** @return list<'device'|'mobile'|'frontend'|'api'|'system'> */
    public static function values(): array
    {
        return [
            self::DEVICE,
            self::MOBILE,
            self::FRONTEND,
            self::API,
            self::SYSTEM,
        ];
    }
}
