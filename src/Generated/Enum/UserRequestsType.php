<?php

declare(strict_types=1);

namespace Clockster\Generated\Enum;

/**
 * What `type` is allowed to be.
 *
 * Sent in a filter on `$clockster->userRequests->list()`.
 *
 * Constants rather than the cases of an enum, so one goes wherever the string goes:
 * `UserRequestsType::LEAVE` is `'leave'`, and a static analyser reads the two as one value. Closed
 * on the way in and only there — an answer naming something this class does not is still a
 * string, and still reaches you.
 */
final class UserRequestsType
{
    public const LEAVE = 'leave';
    public const WORK = 'work';
    public const GENERAL = 'general';
    public const FINANCE = 'finance';

    /** @return list<'leave'|'work'|'general'|'finance'> */
    public static function values(): array
    {
        return [
            self::LEAVE,
            self::WORK,
            self::GENERAL,
            self::FINANCE,
        ];
    }
}
