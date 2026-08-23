<?php

declare(strict_types=1);

namespace Clockster\Generated\Enum;

/**
 * What `include` is allowed to be.
 *
 * Sent in a filter on $clockster->documents->get(), a filter on $clockster->documents->list().
 *
 * Constants rather than the cases of an enum, so one goes wherever the string goes:
 * `DocumentsInclude::ATTACHMENTS` is `'attachments'`, and a static analyser reads the two as one
 * value. Closed on the way in and only there — an answer naming something this class does not is
 * still a string, and still reaches you.
 */
final class DocumentsInclude
{
    public const ATTACHMENTS = 'attachments';
    public const SIGNERS = 'signers';
    public const LABOR_CONTRACT = 'labor_contract';

    /** @return list<'attachments'|'signers'|'labor_contract'> */
    public static function values(): array
    {
        return [
            self::ATTACHMENTS,
            self::SIGNERS,
            self::LABOR_CONTRACT,
        ];
    }
}
