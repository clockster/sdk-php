<?php

declare(strict_types=1);

namespace Clockster\Generated\Enum;

/**
 * What `type` is allowed to be.
 *
 * Sent in a `$clockster->checklists->create()` body.
 *
 * Constants rather than the cases of an enum, so one goes wherever the string goes:
 * `ChecklistsItemType::CHECKBOX` is `'checkbox'`, and a static analyser reads the two as one value.
 * Closed on the way in and only there — an answer naming something this class does not is still a
 * string, and still reaches you.
 */
final class ChecklistsItemType
{
    public const CHECKBOX = 'checkbox';
    public const BOOLEAN = 'boolean';
    public const OPTION = 'option';
    public const NUMBER = 'number';
    public const COMMENT = 'comment';

    /** @return list<'checkbox'|'boolean'|'option'|'number'|'comment'> */
    public static function values(): array
    {
        return [
            self::CHECKBOX,
            self::BOOLEAN,
            self::OPTION,
            self::NUMBER,
            self::COMMENT,
        ];
    }
}
