<?php

declare(strict_types=1);

namespace Clockster\Exception;

/**
 * The call never got an answer — a connection that failed, a timeout, a body that is not the JSON
 * this client expects.
 *
 * A request that times out is unknown rather than failed: it may have been applied. Send it again —
 * a write keyed on an `external_id` converges rather than doubling anything, and the four writes
 * that have no such key take an idempotency key for exactly this.
 */
final class TransportException extends ClocksterException
{
}
