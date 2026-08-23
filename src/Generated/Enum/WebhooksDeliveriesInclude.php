<?php

declare(strict_types=1);

namespace Clockster\Generated\Enum;

/**
 * What `include` is allowed to be.
 *
 * Sent in a filter on $clockster->webhooks->deliveries->list().
 *
 * Constants rather than the cases of an enum, so one goes wherever the string goes:
 * `WebhooksDeliveriesInclude::PAYLOAD` is `'payload'`, and a static analyser reads the two as one
 * value. Closed on the way in and only there — an answer naming something this class does not is
 * still a string, and still reaches you.
 */
final class WebhooksDeliveriesInclude
{
    public const PAYLOAD = 'payload';

    /** @return list<'payload'> */
    public static function values(): array
    {
        return [
            self::PAYLOAD,
        ];
    }
}
