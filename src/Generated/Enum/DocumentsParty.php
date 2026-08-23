<?php

declare(strict_types=1);

namespace Clockster\Generated\Enum;

/**
 * What `party` is allowed to be.
 *
 * Sent in a filter on `$clockster->documents->list()`.
 *
 * Constants rather than the cases of an enum, so one goes wherever the string goes:
 * `DocumentsParty::EMPLOYEE` is `'employee'`, and a static analyser reads the two as one value.
 * Closed on the way in and only there — an answer naming something this class does not is still a
 * string, and still reaches you.
 */
final class DocumentsParty
{
    public const EMPLOYEE = 'employee';
    public const COUNTERPARTY = 'counterparty';

    /** @return list<'employee'|'counterparty'> */
    public static function values(): array
    {
        return [
            self::EMPLOYEE,
            self::COUNTERPARTY,
        ];
    }
}
