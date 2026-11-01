<?php

declare(strict_types=1);

namespace Clockster\Generated\Enum;

/**
 * What `priority` is allowed to be.
 *
 * Sent in a `$clockster->checklists->create()` body.
 *
 * Constants rather than the cases of an enum, so one goes wherever the string goes:
 * `ChecklistsPriority::LOW` is `'low'`, and a static analyser reads the two as one value. Closed on
 * the way in and only there — an answer naming something this class does not is still a string,
 * and still reaches you.
 */
final class ChecklistsPriority
{
    public const LOW = 'low';
    public const NORMAL = 'normal';
    public const HIGH = 'high';

    /** @return list<'low'|'normal'|'high'> */
    public static function values(): array
    {
        return [
            self::LOW,
            self::NORMAL,
            self::HIGH,
        ];
    }
}
