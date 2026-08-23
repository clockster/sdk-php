<?php

declare(strict_types=1);

namespace Clockster\Generated\Enum;

/**
 * What `include` is allowed to be.
 *
 * Sent in a filter on $clockster->userRequests->list().
 *
 * Constants rather than the cases of an enum, so one goes wherever the string goes:
 * `UserRequestsInclude::CONTENT` is `'content'`, and a static analyser reads the two as one value.
 * Closed on the way in and only there — an answer naming something this class does not is still a
 * string, and still reaches you.
 */
final class UserRequestsInclude
{
    public const CONTENT = 'content';
    public const USER = 'user';
    public const AUTHOR = 'author';

    /** @return list<'content'|'user'|'author'> */
    public static function values(): array
    {
        return [
            self::CONTENT,
            self::USER,
            self::AUTHOR,
        ];
    }
}
