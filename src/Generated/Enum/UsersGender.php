<?php

declare(strict_types=1);

namespace Clockster\Generated\Enum;

/**
 * What `gender` is allowed to be.
 *
 * Sent in a `$clockster->users->upsert()` body.
 *
 * Constants rather than the cases of an enum, so one goes wherever the string goes:
 * `UsersGender::MALE` is `'male'`, and a static analyser reads the two as one value. Closed on the
 * way in and only there — an answer naming something this class does not is still a string, and
 * still reaches you.
 */
final class UsersGender
{
    public const MALE = 'male';
    public const FEMALE = 'female';
    public const OTHER = 'other';

    /** @return list<'male'|'female'|'other'> */
    public static function values(): array
    {
        return [
            self::MALE,
            self::FEMALE,
            self::OTHER,
        ];
    }
}
