<?php

declare(strict_types=1);

namespace Clockster\Generated\Enum;

/**
 * What `status` is allowed to be.
 *
 * Sent in a filter on `$clockster->users->list()`.
 *
 * Constants rather than the cases of an enum, so one goes wherever the string goes:
 * `UsersStatus::ACTIVE` is `'active'`, and a static analyser reads the two as one value. Closed on
 * the way in and only there — an answer naming something this class does not is still a string,
 * and still reaches you.
 */
final class UsersStatus
{
    public const ACTIVE = 'active';
    public const DISMISSED = 'dismissed';
    public const ALL = 'all';

    /** @return list<'active'|'dismissed'|'all'> */
    public static function values(): array
    {
        return [
            self::ACTIVE,
            self::DISMISSED,
            self::ALL,
        ];
    }
}
