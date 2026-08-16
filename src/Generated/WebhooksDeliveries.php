<?php

declare(strict_types=1);

namespace Clockster\Generated;

use Clockster\Exception\ApiException;
use Clockster\Exception\TransportException;
use Clockster\Http\Caller;
use Generator;

/**
 * The operations of `$clockster->webhooks->deliveries`.
 *
 * @phpstan-import-type WebhooksDeliveriesGetResponse from Shapes
 * @phpstan-import-type WebhooksDeliveriesListResponse from Shapes
 * @phpstan-import-type WebhooksDeliveriesListRow from Shapes
 * @phpstan-import-type WebhooksDeliveriesRedeliverResponse from Shapes
 */
final class WebhooksDeliveries
{
    public function __construct(private readonly Caller $caller)
    {
    }

    /**
     * Read one webhook delivery.
     *
     * Answers with the payload, unlike the listing: one row is not a hundred employee records.
     *
     * @param int $id The id of the row, as this API issued it.
     *
     * @return WebhooksDeliveriesGetResponse
     *
     * @throws ApiException|TransportException
     */
    public function get(int $id): array
    {
        /** @var WebhooksDeliveriesGetResponse $answer */
        $answer = $this->caller->call(
            'GET',
            sprintf('/company/v3/webhooks/deliveries/%d', $id),
        );

        return $answer;
    }

    /**
     * List webhook deliveries.
     *
     * What was sent, and what came of it. **Newest first**, unlike every other listing here: a
     * delivery log is read to learn what just happened.
     *
     * `state` is the field to branch on. `delivered` and `failed` are final; `pending` is
     * neither — the event is waiting out its backoff and will be tried again. Reading
     * `is_successful: false` as failure is the mistake the two stored flags invite.
     *
     * `payload` is behind `include=payload` and off by default: a page of a hundred deliveries
     * is a hundred employee records, and a caller watching its integration's health has no use
     * for them. Reading one delivery answers with it either way.
     *
     * `webhook_id` is null where the endpoint has since been removed.
     *
     * @param int|null $perPage How many rows one page holds. Defaults to 50.
     * @param string|null $cursor The `meta.next_cursor` of the previous page. Omit it for the first. A cursor is bound to the filters it was issued under — change them and start again.
     * @param list<int> $webhooks Only deliveries to these endpoints, by id.
     * @param list<string> $events Only deliveries of these events.
     * @param bool|null $successful Only deliveries that were accepted (`true`) or that were not (`false`).
     * @param bool|null $pending Only deliveries still waiting on a retry (`true`), or only those finished with (`false`).
     * @param string|null $since Only rows from this instant onward (ISO 8601).
     * @param list<string> $include Relations to load, comma-separated. Anything not named is absent
     * from the answer rather than null.
     *
     * @return WebhooksDeliveriesListResponse
     *
     * @throws ApiException|TransportException
     */
    public function list(
        ?int $perPage = null,
        ?string $cursor = null,
        array $webhooks = [],
        array $events = [],
        ?bool $successful = null,
        ?bool $pending = null,
        ?string $since = null,
        array $include = [],
    ): array {
        /** @var WebhooksDeliveriesListResponse $answer */
        $answer = $this->caller->call(
            'GET',
            '/company/v3/webhooks/deliveries',
            [
                'per_page' => $perPage,
                'cursor' => $cursor,
                'webhooks' => $webhooks,
                'events' => $events,
                'successful' => $successful,
                'pending' => $pending,
                'since' => $since,
                'include' => $include,
            ],
        );

        return $answer;
    }

    /**
     * Every row of list(), a page at a time.
     *
     * A refused page is thrown where it was refused, so half a listing is never mistaken for the
     * whole of one. A cursor belongs to the filters it was issued under: change them and walk
     * again.
     *
     * @param int|null $perPage How many rows one page holds. Defaults to 50.
     * @param list<int> $webhooks Only deliveries to these endpoints, by id.
     * @param list<string> $events Only deliveries of these events.
     * @param bool|null $successful Only deliveries that were accepted (`true`) or that were not (`false`).
     * @param bool|null $pending Only deliveries still waiting on a retry (`true`), or only those finished with (`false`).
     * @param string|null $since Only rows from this instant onward (ISO 8601).
     * @param list<string> $include Relations to load, comma-separated. Anything not named is absent
     * from the answer rather than null.
     *
     * @return \Generator<int, WebhooksDeliveriesListRow>
     *
     * @throws ApiException|TransportException
     */
    public function listAll(
        ?int $perPage = null,
        array $webhooks = [],
        array $events = [],
        ?bool $successful = null,
        ?bool $pending = null,
        ?string $since = null,
        array $include = [],
    ): Generator {
        $cursor = null;
        $seen = [];

        while (true) {
            $page = $this->list(
                perPage: $perPage,
                webhooks: $webhooks,
                events: $events,
                successful: $successful,
                pending: $pending,
                since: $since,
                include: $include,
                cursor: $cursor,
            );

            foreach ($page['data'] as $row) {
                yield $row;
            }

            $cursor = $page['meta']['next_cursor'] ?? null;

            // A cursor that repeats would page until the process is killed, which is worse
            // than stopping.
            if ($cursor === null || isset($seen[$cursor])) {
                return;
            }

            $seen[$cursor] = true;
        }
    }

    /**
     * Send a delivery again.
     *
     * Queues the recorded event for another attempt, and answers once queued rather than once
     * delivered.
     *
     * A repair tool, now that transient failures retry themselves: this is for the case where
     * the receiver was fixed after we had already given up. What goes out is the event as it
     * was recorded, so `occurred_at` may be long past — which is why the envelope states it.
     *
     * @param int $id The id of the row, as this API issued it.
     * @param string|null $idempotencyKey A value of your own, so a retry of this write is answered with the first result rather than performed again.
     *
     * @return WebhooksDeliveriesRedeliverResponse
     *
     * @throws ApiException|TransportException
     */
    public function redeliver(int $id, ?string $idempotencyKey = null): array
    {
        /** @var WebhooksDeliveriesRedeliverResponse $answer */
        $answer = $this->caller->call(
            'POST',
            sprintf('/company/v3/webhooks/deliveries/%d/redeliver', $id),
            idempotencyKey: $idempotencyKey,
        );

        return $answer;
    }
}
