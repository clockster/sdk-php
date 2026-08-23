<?php

declare(strict_types=1);

namespace Clockster\Generated\Enum;

/**
 * What `include` is allowed to be.
 *
 * Sent in a filter on `$clockster->tasks->get()`, a filter on `$clockster->tasks->list()`.
 *
 * Constants rather than the cases of an enum, so one goes wherever the string goes:
 * `TasksInclude::ITEMS` is `'items'`, and a static analyser reads the two as one value. Closed on
 * the way in and only there — an answer naming something this class does not is still a string,
 * and still reaches you.
 */
final class TasksInclude
{
    public const ITEMS = 'items';
    public const MANAGERS = 'managers';
    public const USER = 'user';
    public const AUTHOR = 'author';

    /** @return list<'items'|'managers'|'user'|'author'> */
    public static function values(): array
    {
        return [
            self::ITEMS,
            self::MANAGERS,
            self::USER,
            self::AUTHOR,
        ];
    }
}
