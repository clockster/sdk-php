<?php

declare(strict_types=1);

namespace Clockster\Exception;

/**
 * A delivery that is not provably ours, or is too old to act on.
 */
final class WebhookVerificationException extends ClocksterException
{
    public function __construct(string $message, private readonly string $reason)
    {
        parent::__construct($message);
    }

    /**
     * Why it was refused, for a log or a metric: `missing_signature`, `missing_timestamp`,
     * `unknown_scheme`, `signature_mismatch`, `timestamp_unreadable`, `timestamp_outside_tolerance`
     * or `body_unparseable`.
     */
    public function reason(): string
    {
        return $this->reason;
    }
}
