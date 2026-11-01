<?php

declare(strict_types=1);

namespace Clockster\Generated\Enum;

/**
 * What `type` is allowed to be.
 *
 * Sent in a `$clockster->checklists->create()` body, a filter on `$clockster->checklists->list()`.
 *
 * Constants rather than the cases of an enum, so one goes wherever the string goes:
 * `ChecklistsType::DEFAULT` is `'default'`, and a static analyser reads the two as one value.
 * Closed on the way in and only there — an answer naming something this class does not is still a
 * string, and still reaches you.
 */
final class ChecklistsType
{
    public const DEFAULT = 'default';
    public const AUDIT = 'audit';

    /** @return list<'default'|'audit'> */
    public static function values(): array
    {
        return [
            self::DEFAULT,
            self::AUDIT,
        ];
    }
}
