<?php

declare(strict_types=1);

namespace Clockster\Generated\Enum;

/**
 * What `employment` is allowed to be.
 *
 * Sent in a `$clockster->users->upsert()` body, a filter on `$clockster->users->list()`.
 *
 * Constants rather than the cases of an enum, so one goes wherever the string goes:
 * `UsersEmployment::FULL_TIME` is `'full_time'`, and a static analyser reads the two as one value.
 * Closed on the way in and only there — an answer naming something this class does not is still a
 * string, and still reaches you.
 */
final class UsersEmployment
{
    public const FULL_TIME = 'full_time';
    public const PART_TIME = 'part_time';
    public const IRREGULAR_HOURS = 'irregular_hours';
    public const CONTRACT_1 = 'contract_1';
    public const CONTRACT_2 = 'contract_2';
    public const APPRENTICESHIP = 'apprenticeship';
    public const TRAINEESHIP = 'traineeship';
    public const PIECE_RATE = 'piece_rate';
    public const PROBATION = 'probation';
    public const OUTSTAFFING = 'outstaffing';

    /** @return list<'full_time'|'part_time'|'irregular_hours'|'contract_1'|'contract_2'|'apprenticeship'|'traineeship'|'piece_rate'|'probation'|'outstaffing'> */
    public static function values(): array
    {
        return [
            self::FULL_TIME,
            self::PART_TIME,
            self::IRREGULAR_HOURS,
            self::CONTRACT_1,
            self::CONTRACT_2,
            self::APPRENTICESHIP,
            self::TRAINEESHIP,
            self::PIECE_RATE,
            self::PROBATION,
            self::OUTSTAFFING,
        ];
    }
}
