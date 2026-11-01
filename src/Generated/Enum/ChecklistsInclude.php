<?php

declare(strict_types=1);

namespace Clockster\Generated\Enum;

/**
 * What `include` is allowed to be.
 *
 * Sent in a filter on `$clockster->checklists->get()`, a filter on
 * `$clockster->checklists->list()`.
 *
 * Constants rather than the cases of an enum, so one goes wherever the string goes:
 * `ChecklistsInclude::ITEMS` is `'items'`, and a static analyser reads the two as one value. Closed
 * on the way in and only there — an answer naming something this class does not is still a
 * string, and still reaches you.
 */
final class ChecklistsInclude
{
    public const ITEMS = 'items';
    public const MANAGERS = 'managers';
    public const USERS = 'users';
    public const ISSUES = 'issues';
    public const COMMENTS = 'comments';
    public const ATTACHMENTS = 'attachments';
    public const AUTHOR = 'author';

    /** @return list<'items'|'managers'|'users'|'issues'|'comments'|'attachments'|'author'> */
    public static function values(): array
    {
        return [
            self::ITEMS,
            self::MANAGERS,
            self::USERS,
            self::ISSUES,
            self::COMMENTS,
            self::ATTACHMENTS,
            self::AUTHOR,
        ];
    }
}
