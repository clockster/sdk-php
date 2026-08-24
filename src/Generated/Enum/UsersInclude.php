<?php

declare(strict_types=1);

namespace Clockster\Generated\Enum;

/**
 * What `include` is allowed to be.
 *
 * Sent in a filter on `$clockster->users->get()`, a filter on `$clockster->users->list()`.
 *
 * Constants rather than the cases of an enum, so one goes wherever the string goes:
 * `UsersInclude::LOCATION` is `'location'`, and a static analyser reads the two as one value.
 * Closed on the way in and only there — an answer naming something this class does not is still a
 * string, and still reaches you.
 */
final class UsersInclude
{
    public const LOCATION = 'location';
    public const LOCATIONS = 'locations';
    public const DEPARTMENT = 'department';
    public const POSITION = 'position';
    public const USER_FILTERS = 'user_filters';
    public const DISMISSAL = 'dismissal';
    public const META = 'meta';

    /** @return list<'location'|'locations'|'department'|'position'|'user_filters'|'dismissal'|'meta'> */
    public static function values(): array
    {
        return [
            self::LOCATION,
            self::LOCATIONS,
            self::DEPARTMENT,
            self::POSITION,
            self::USER_FILTERS,
            self::DISMISSAL,
            self::META,
        ];
    }
}
