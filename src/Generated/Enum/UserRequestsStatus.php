<?php

declare(strict_types=1);

namespace Clockster\Generated\Enum;

/**
 * What `status` is allowed to be.
 *
 * Sent in a filter on `$clockster->userRequests->list()`.
 *
 * Constants rather than the cases of an enum, so one goes wherever the string goes:
 * `UserRequestsStatus::PENDING` is `'pending'`, and a static analyser reads the two as one value.
 * Closed on the way in and only there — an answer naming something this class does not is still a
 * string, and still reaches you.
 */
final class UserRequestsStatus
{
    public const PENDING = 'pending';
    public const ACCEPTED = 'accepted';
    public const REJECTED = 'rejected';
    public const CANCELLED = 'cancelled';
    public const APPROVAL = 'approval';
    public const EXECUTION = 'execution';
    public const SIGNING = 'signing';

    /** @return list<'pending'|'accepted'|'rejected'|'cancelled'|'approval'|'execution'|'signing'> */
    public static function values(): array
    {
        return [
            self::PENDING,
            self::ACCEPTED,
            self::REJECTED,
            self::CANCELLED,
            self::APPROVAL,
            self::EXECUTION,
            self::SIGNING,
        ];
    }
}
