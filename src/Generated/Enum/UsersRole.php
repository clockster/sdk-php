<?php

declare(strict_types=1);

namespace Clockster\Generated\Enum;

/**
 * What `role` is allowed to be.
 *
 * Sent in a $clockster->users->upsert() body.
 *
 * Constants rather than the cases of an enum, so one goes wherever the string goes:
 * `UsersRole::ADMIN` is `'admin'`, and a static analyser reads the two as one value. Closed on the
 * way in and only there — an answer naming something this class does not is still a string, and
 * still reaches you.
 */
final class UsersRole
{
    public const ADMIN = 'admin';
    public const EMPLOYEE = 'employee';

    /** @return list<'admin'|'employee'> */
    public static function values(): array
    {
        return [
            self::ADMIN,
            self::EMPLOYEE,
        ];
    }
}
