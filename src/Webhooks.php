<?php

declare(strict_types=1);

namespace Clockster;

use Clockster\Exception\WebhookVerificationException;
use DateTimeImmutable;
use Exception;
use JsonException;

/**
 * Verifying a delivery, and reading the event out of it.
 *
 * The check is the only way to the event: verify() takes the body as it arrived and answers the
 * parsed event, so there is no path that acts on one that was not verified.
 *
 *     $event = Clockster\Webhooks::verifyGlobals($secret);
 *
 *     http_response_code(200);
 *
 * Answer 2xx quickly and do the work afterwards — a timeout is retried — and deduplicate on the
 * event's `id`, since the same event may arrive twice.
 */
final class Webhooks
{
    /** The headers a delivery carries. */
    public const HEADER_SIGNATURE = 'X-Clockster-Signature';
    public const HEADER_TIMESTAMP = 'X-Clockster-Timestamp';
    public const HEADER_EVENT = 'X-Clockster-Event';
    public const HEADER_DELIVERY = 'X-Clockster-Delivery';

    private const SCHEME = 'sha256=';

    /** Seconds. Refusing an old delivery is what stops a replay. */
    public const DEFAULT_TOLERANCE = 300;

    /**
     * Verify a delivery and answer the event it carries.
     *
     * @param string      $body      the body as received; re-serialising a parsed object does not
     *                               reproduce the signed bytes and the check will fail
     * @param string|null $signature the X-Clockster-Signature header
     * @param string|null $timestamp the X-Clockster-Timestamp header, an ISO 8601 instant rather
     *                               than a Unix time
     * @param string      $secret    the signing secret of the endpoint
     * @param int         $tolerance maximum age in seconds; 0 accepts a delivery of any age, which
     *                               is worth having in a test and not in production
     *
     * @return array{id: int|null, event: string, occurred_at: string, data: mixed}
     *
     * @throws WebhookVerificationException when the delivery is not provably ours, or is too old
     */
    public static function verify(
        string $body,
        ?string $signature,
        ?string $timestamp,
        string $secret,
        int $tolerance = self::DEFAULT_TOLERANCE,
    ): array {
        self::check($body, $signature, $timestamp, $secret, $tolerance);

        try {
            $event = json_decode($body, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $failure) {
            throw new WebhookVerificationException(
                'The body is signed but is not JSON: ' . $failure->getMessage(),
                'body_unparseable',
            );
        }

        if (!is_array($event)) {
            throw new WebhookVerificationException('The body is signed but is not an event.', 'body_unparseable');
        }

        /** @var array{id: int|null, event: string, occurred_at: string, data: mixed} $event */
        return $event;
    }

    /**
     * verify(), reading the delivery out of the current request.
     *
     * For plain PHP. A framework hands you the body and the headers already — use verify() there,
     * with `$request->getContent()` rather than anything re-encoded.
     *
     * @return array{id: int|null, event: string, occurred_at: string, data: mixed}
     *
     * @throws WebhookVerificationException
     */
    public static function verifyGlobals(string $secret, int $tolerance = self::DEFAULT_TOLERANCE): array
    {
        $body = file_get_contents('php://input');

        return self::verify(
            $body === false ? '' : $body,
            self::server('HTTP_X_CLOCKSTER_SIGNATURE'),
            self::server('HTTP_X_CLOCKSTER_TIMESTAMP'),
            $secret,
            $tolerance,
        );
    }

    private static function server(string $key): ?string
    {
        $value = $_SERVER[$key] ?? null;

        return is_string($value) ? $value : null;
    }

    private static function check(
        string $body,
        ?string $signature,
        ?string $timestamp,
        string $secret,
        int $tolerance,
    ): void {
        if ($signature === null || $signature === '') {
            throw new WebhookVerificationException('No ' . self::HEADER_SIGNATURE . ' header.', 'missing_signature');
        }

        if ($timestamp === null || $timestamp === '') {
            throw new WebhookVerificationException('No ' . self::HEADER_TIMESTAMP . ' header.', 'missing_timestamp');
        }

        if (!str_starts_with($signature, self::SCHEME)) {
            throw new WebhookVerificationException('The signature is not ' . self::SCHEME . '<hex>.', 'unknown_scheme');
        }

        // The timestamp is inside what is signed, so it cannot be edited to widen the check below.
        $expected = hash_hmac('sha256', $timestamp . '.' . $body, $secret);

        if (!hash_equals($expected, substr($signature, strlen(self::SCHEME)))) {
            throw new WebhookVerificationException(
                'The signature does not match the body. Verify the bytes as received, before parsing them.',
                'signature_mismatch',
            );
        }

        self::fresh($timestamp, $tolerance);
    }

    private static function fresh(string $timestamp, int $tolerance): void
    {
        if ($tolerance <= 0) {
            return;
        }

        try {
            $sent = new DateTimeImmutable($timestamp);
        } catch (Exception) {
            throw new WebhookVerificationException(
                sprintf('The timestamp "%s" is not an ISO 8601 instant.', $timestamp),
                'timestamp_unreadable',
            );
        }

        // Absolute, so a receiver whose clock runs behind refuses rather than accepting
        // indefinitely.
        if (abs(time() - $sent->getTimestamp()) > $tolerance) {
            throw new WebhookVerificationException(
                sprintf('The delivery is outside the %ds tolerance.', $tolerance),
                'timestamp_outside_tolerance',
            );
        }
    }
}
