<?php

declare(strict_types=1);

namespace Clockster\Generated\Enum;

/**
 * What `leave_type` is allowed to be.
 *
 * Sent in a $clockster->schedules->create() body.
 *
 * Constants rather than the cases of an enum, so one goes wherever the string goes:
 * `SchedulesLeaveType::ANNUAL` is `'annual'`, and a static analyser reads the two as one value.
 * Closed on the way in and only there — an answer naming something this class does not is still a
 * string, and still reaches you.
 */
final class SchedulesLeaveType
{
    public const ANNUAL = 'annual';
    public const UNPAID = 'unpaid';
    public const SICK = 'sick';
    public const UNPAID_SICK = 'unpaid_sick';
    public const MATERNITY = 'maternity';
    public const PATERNITY = 'paternity';
    public const SPECIAL = 'special';
    public const DAY_OFF = 'day_off';
    public const COMPENSATORY = 'compensatory';
    public const PERSONAL = 'personal';
    public const EMERGENCY = 'emergency';
    public const UNEXCUSED_ABSENCE = 'unexcused_absence';

    /** @return list<'annual'|'unpaid'|'sick'|'unpaid_sick'|'maternity'|'paternity'|'special'|'day_off'|'compensatory'|'personal'|'emergency'|'unexcused_absence'> */
    public static function values(): array
    {
        return [
            self::ANNUAL,
            self::UNPAID,
            self::SICK,
            self::UNPAID_SICK,
            self::MATERNITY,
            self::PATERNITY,
            self::SPECIAL,
            self::DAY_OFF,
            self::COMPENSATORY,
            self::PERSONAL,
            self::EMERGENCY,
            self::UNEXCUSED_ABSENCE,
        ];
    }
}
