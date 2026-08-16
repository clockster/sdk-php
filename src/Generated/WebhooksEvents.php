<?php

declare(strict_types=1);

namespace Clockster\Generated;

use Clockster\Exception\ApiException;
use Clockster\Exception\TransportException;
use Clockster\Http\Caller;

/**
 * The operations of `$clockster->webhooks->events`.
 *
 * @phpstan-import-type WebhooksEventsListResponse from Shapes
 */
final class WebhooksEvents
{
    public function __construct(private readonly Caller $caller)
    {
    }

    /**
     * List subscribable events.
     *
     * Every event name an endpoint can subscribe to.
     *
     * Served as well as specified, so a caller can check at runtime that a name it stored is
     * still one we send rather than discovering it on the next save.
     *
     * @return WebhooksEventsListResponse
     *
     * @throws ApiException|TransportException
     */
    public function list(): array
    {
        /** @var WebhooksEventsListResponse $answer */
        $answer = $this->caller->call(
            'GET',
            '/company/v3/webhooks/events',
        );

        return $answer;
    }
}
