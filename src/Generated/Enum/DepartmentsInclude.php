<?php

declare(strict_types=1);

namespace Clockster\Generated\Enum;

/**
 * What `include` is allowed to be.
 *
 * Sent in a filter on `$clockster->departments->get()`, a filter on
 * `$clockster->departments->list()`.
 *
 * Constants rather than the cases of an enum, so one goes wherever the string goes:
 * `DepartmentsInclude::MANAGERS` is `'managers'`, and a static analyser reads the two as one value.
 * Closed on the way in and only there — an answer naming something this class does not is still a
 * string, and still reaches you.
 */
final class DepartmentsInclude
{
    public const MANAGERS = 'managers';

    /** @return list<'managers'> */
    public static function values(): array
    {
        return [
            self::MANAGERS,
        ];
    }
}
