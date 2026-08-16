<?php

declare(strict_types=1);

namespace Clockster\Generated;

use Clockster\Exception\ApiException;
use Clockster\Exception\TransportException;
use Clockster\Http\Caller;
use Generator;

/**
 * The operations of `$clockster->webhooks`.
 *
 * @phpstan-import-type WebhooksCreateBody from Shapes
 * @phpstan-import-type WebhooksCreateResponse from Shapes
 * @phpstan-import-type WebhooksDeleteResponse from Shapes
 * @phpstan-import-type WebhooksGetResponse from Shapes
 * @phpstan-import-type WebhooksListResponse from Shapes
 * @phpstan-import-type WebhooksListRow from Shapes
 * @phpstan-import-type WebhooksRotateSecretResponse from Shapes
 * @phpstan-import-type WebhooksUpdateBody from Shapes
 * @phpstan-import-type WebhooksUpdateResponse from Shapes
 */
final class Webhooks
{
    /** The operations of `$clockster->webhooks->deliveries`. */
    public readonly WebhooksDeliveries $deliveries;

    /** The operations of `$clockster->webhooks->events`. */
    public readonly WebhooksEvents $events;

    public function __construct(private readonly Caller $caller)
    {
        $this->deliveries = new WebhooksDeliveries($caller);
        $this->events = new WebhooksEvents($caller);
    }

    /**
     * Create a webhook endpoint.
     *
     * Connect an endpoint. Answers 201 with the signing secret it will use.
     *
     * **No `external_id`, and no upsert**, unlike every other write on this surface. Those
     * mirror something your system already holds and must match on its own key; an endpoint is
     * created here and its identity is ours, so there is nothing to match against.
     *
     * The secret is generated, never accepted: a caller-chosen signing key is a caller-chosen
     * weakness. Replace it with `POST /company/v3/webhooks/{id}/secret`.
     *
     * Deliveries carry `X-Clockster-Event`, `X-Clockster-Delivery` (constant across retries,
     * so a repeat can be recognised), `X-Clockster-Timestamp` and `X-Clockster-Signature` —
     * `sha256=` HMAC-SHA256 of `timestamp + "." + rawBody` under the secret. The body is
     * `{"id", "event", "occurred_at", "data"}`.
     *
     * @param WebhooksCreateBody $body
     * @param string|null $idempotencyKey A value of your own, so a retry of this write is answered with the first result rather than performed again.
     *
     * @return WebhooksCreateResponse
     *
     * @throws ApiException|TransportException
     */
    public function create(array $body, ?string $idempotencyKey = null): array
    {
        /** @var WebhooksCreateResponse $answer */
        $answer = $this->caller->call(
            'POST',
            '/company/v3/webhooks',
            body: $body,
            idempotencyKey: $idempotencyKey,
        );

        return $answer;
    }

    /**
     * Delete a webhook endpoint.
     *
     * Removes the endpoint. What was delivered to it stays readable: the delivery's link is
     * nulled rather than cascaded, because the record of what was sent is the company's.
     *
     * @param int $id The id of the row, as this API issued it.
     *
     * @return WebhooksDeleteResponse
     *
     * @throws ApiException|TransportException
     */
    public function delete(int $id): array
    {
        /** @var WebhooksDeleteResponse $answer */
        $answer = $this->caller->call(
            'DELETE',
            sprintf('/company/v3/webhooks/%d', $id),
        );

        return $answer;
    }

    /**
     * Read one webhook endpoint.
     *
     * @param int $id The id of the row, as this API issued it.
     *
     * @return WebhooksGetResponse
     *
     * @throws ApiException|TransportException
     */
    public function get(int $id): array
    {
        /** @var WebhooksGetResponse $answer */
        $answer = $this->caller->call(
            'GET',
            sprintf('/company/v3/webhooks/%d', $id),
        );

        return $answer;
    }

    /**
     * List webhook endpoints.
     *
     * The endpoints this company has connected, and the health of each.
     *
     * `health` answers what `active` cannot: whether a person switched an endpoint off or a
     * run of failures did. Five consecutive permanent failures — a wrong address, refused
     * credentials — switch it off, as do twenty transient ones. `disabled_reason` says which.
     *
     * `secret` is answered in full because verifying a signature is impossible without it.
     * The credential we authenticate to the receiver *with* is not: `auth` names the scheme
     * and, for basic, the username, and never the password or bearer token.
     *
     * @param int|null $perPage How many rows one page holds. Defaults to 50.
     * @param string|null $cursor The `meta.next_cursor` of the previous page. Omit it for the first. A cursor is bound to the filters it was issued under — change them and start again.
     * @param bool|null $active Only rows switched on (`true`) or off (`false`). Omit for both.
     *
     * @return WebhooksListResponse
     *
     * @throws ApiException|TransportException
     */
    public function list(?int $perPage = null, ?string $cursor = null, ?bool $active = null): array
    {
        /** @var WebhooksListResponse $answer */
        $answer = $this->caller->call(
            'GET',
            '/company/v3/webhooks',
            [
                'per_page' => $perPage,
                'cursor' => $cursor,
                'active' => $active,
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
     * @param bool|null $active Only rows switched on (`true`) or off (`false`). Omit for both.
     *
     * @return \Generator<int, WebhooksListRow>
     *
     * @throws ApiException|TransportException
     */
    public function listAll(?int $perPage = null, ?bool $active = null): Generator
    {
        $cursor = null;
        $seen = [];

        while (true) {
            $page = $this->list(perPage: $perPage, active: $active, cursor: $cursor);

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
     * Replace the signing secret.
     *
     * Answers the endpoint with a new secret.
     *
     * **Send an `Idempotency-Key`.** The secret is shown once, so a retry after a lost
     * response would rotate a second time and leave you holding one that signs nothing — with
     * the header, the retry is answered with the secret the first call minted.
     *
     * Deliveries already queued are signed with whichever secret is current when they are
     * actually sent, so accept both for as long as your backlog can be deep — up to about a
     * day where transient failures are being retried.
     *
     * @param int $id The id of the row, as this API issued it.
     * @param string|null $idempotencyKey A value of your own, so a retry of this write is answered with the first result rather than performed again.
     *
     * @return WebhooksRotateSecretResponse
     *
     * @throws ApiException|TransportException
     */
    public function rotateSecret(int $id, ?string $idempotencyKey = null): array
    {
        /** @var WebhooksRotateSecretResponse $answer */
        $answer = $this->caller->call(
            'POST',
            sprintf('/company/v3/webhooks/%d/secret', $id),
            idempotencyKey: $idempotencyKey,
        );

        return $answer;
    }

    /**
     * Replace a webhook endpoint.
     *
     * Replaces rather than patches: half a subscription is not a state worth reaching by
     * accident.
     *
     * Saving clears the failure tally and any automatic switch-off — you are saying something
     * changed, so what the history counted no longer describes what is there. This is how an
     * endpoint switched off by repeated failures is put back into service.
     *
     * @param int $id The id of the row, as this API issued it.
     * @param WebhooksUpdateBody $body
     *
     * @return WebhooksUpdateResponse
     *
     * @throws ApiException|TransportException
     */
    public function update(int $id, array $body): array
    {
        /** @var WebhooksUpdateResponse $answer */
        $answer = $this->caller->call(
            'PUT',
            sprintf('/company/v3/webhooks/%d', $id),
            body: $body,
        );

        return $answer;
    }
}
