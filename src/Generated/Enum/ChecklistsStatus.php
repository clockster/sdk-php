<?php

declare(strict_types=1);

namespace Clockster\Generated\Enum;

/**
 * What `status` is allowed to be.
 *
 * Sent in a filter on `$clockster->checklists->list()`.
 *
 * Constants rather than the cases of an enum, so one goes wherever the string goes:
 * `ChecklistsStatus::CREATED` is `'created'`, and a static analyser reads the two as one value.
 * Closed on the way in and only there — an answer naming something this class does not is still a
 * string, and still reaches you.
 */
final class ChecklistsStatus
{
    public const CREATED = 'created';
    public const STARTED = 'started';
    public const COMPLETED = 'completed';
    public const INCOMPLETED = 'incompleted';
    public const PASTDUE = 'pastdue';
    public const PENDING = 'pending';

    /** @return list<'created'|'started'|'completed'|'incompleted'|'pastdue'|'pending'> */
    public static function values(): array
    {
        return [
            self::CREATED,
            self::STARTED,
            self::COMPLETED,
            self::INCOMPLETED,
            self::PASTDUE,
            self::PENDING,
        ];
    }
}
