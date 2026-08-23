<?php

declare(strict_types=1);

namespace Clockster\Generated\Enum;

/**
 * What `type` is allowed to be.
 *
 * Sent in a $clockster->schedules->create() body.
 *
 * Constants rather than the cases of an enum, so one goes wherever the string goes:
 * `SchedulesType::WORK` is `'work'`, and a static analyser reads the two as one value. Closed on
 * the way in and only there — an answer naming something this class does not is still a string,
 * and still reaches you.
 */
final class SchedulesType
{
    public const WORK = 'work';
    public const FREE = 'free';
    public const LEAVE = 'leave';

    /** @return list<'work'|'free'|'leave'> */
    public static function values(): array
    {
        return [
            self::WORK,
            self::FREE,
            self::LEAVE,
        ];
    }
}
