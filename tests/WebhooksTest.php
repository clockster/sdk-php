<?php

declare(strict_types=1);

namespace Clockster\Tests;

use Clockster\Exception\WebhookVerificationException;
use Clockster\Webhooks;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * Verifying a delivery, against one this test signed rather than one that arrived.
 */
#[CoversNothing]
final class WebhooksTest extends TestCase
{
    private const SECRET = 'whsec_test';

    private function sign(string $body, string $timestamp): string
    {
        return 'sha256=' . hash_hmac('sha256', $timestamp . '.' . $body, self::SECRET);
    }

    private function now(): string
    {
        return gmdate('Y-m-d\TH:i:sP');
    }

    private function refusal(callable $call): WebhookVerificationException
    {
        try {
            $call();
        } catch (WebhookVerificationException $refused) {
            return $refused;
        }

        self::fail('The delivery was accepted.');
    }

    public function testVerifiesADeliveryAndAnswersTheEvent(): void
    {
        $body = '{"id":123,"event":"user.updated","occurred_at":"2026-03-02T09:00:00+05:00","data":{"id":7}}';
        $timestamp = $this->now();

        $event = Webhooks::verify($body, $this->sign($body, $timestamp), $timestamp, self::SECRET);

        self::assertSame(123, $event['id']);
        self::assertSame('user.updated', $event['event']);
        self::assertSame(['id' => 7], $event['data']);
    }

    public function testATrialDeliveryCarriesNoEventId(): void
    {
        $body = '{"id":null,"event":"ping","occurred_at":"2026-03-02T09:00:00+00:00","data":{}}';
        $timestamp = $this->now();

        $event = Webhooks::verify($body, $this->sign($body, $timestamp), $timestamp, self::SECRET);

        // Null stands for no recorded event, which is what a trial delivery is.
        self::assertNull($event['id']);
    }

    public function testRefusesABodyThatWasChanged(): void
    {
        $body = '{"id":1,"event":"user.updated","occurred_at":"2026-03-02T09:00:00+00:00","data":{}}';
        $timestamp = $this->now();
        $signature = $this->sign($body, $timestamp);

        $refused = $this->refusal(static fn () => Webhooks::verify(
            str_replace('"id":1', '"id":2', $body),
            $signature,
            $timestamp,
            self::SECRET,
        ));

        self::assertSame('signature_mismatch', $refused->reason());
    }

    public function testRefusesADeliveryUnderAnotherSecret(): void
    {
        $body = '{"id":1,"event":"user.updated","occurred_at":"2026-03-02T09:00:00+00:00","data":{}}';
        $timestamp = $this->now();

        $refused = $this->refusal(fn () => Webhooks::verify(
            $body,
            $this->sign($body, $timestamp),
            $timestamp,
            'whsec_somebody_else',
        ));

        self::assertSame('signature_mismatch', $refused->reason());
    }

    public function testTheTimestampIsInsideWhatIsSigned(): void
    {
        $body = '{"id":1,"event":"user.updated","occurred_at":"2026-03-02T09:00:00+00:00","data":{}}';
        $timestamp = $this->now();
        $signature = $this->sign($body, $timestamp);

        // Moving the timestamp to widen the tolerance breaks the signature instead.
        $refused = $this->refusal(static fn () => Webhooks::verify(
            $body,
            $signature,
            gmdate('Y-m-d\TH:i:sP', time() - 60),
            self::SECRET,
        ));

        self::assertSame('signature_mismatch', $refused->reason());
    }

    public function testRefusesWhatIsMissing(): void
    {
        $body = '{"id":1,"event":"user.updated","occurred_at":"2026-03-02T09:00:00+00:00","data":{}}';
        $timestamp = $this->now();
        $signature = $this->sign($body, $timestamp);

        $cases = [
            'missing_signature' => [null, $timestamp],
            'missing_timestamp' => [$signature, null],
            'unknown_scheme' => ['md5=abc', $timestamp],
        ];

        foreach ($cases as $expected => [$sent, $sentAt]) {
            $refused = $this->refusal(static fn () => Webhooks::verify($body, $sent, $sentAt, self::SECRET));

            self::assertSame($expected, $refused->reason());
        }
    }

    public function testRefusesADeliveryOutsideTheTolerance(): void
    {
        $body = '{"id":1,"event":"user.updated","occurred_at":"2026-03-02T09:00:00+00:00","data":{}}';
        $timestamp = gmdate('Y-m-d\TH:i:sP', time() - 3600);
        $signature = $this->sign($body, $timestamp);

        // Signed by us and an hour old: refusing it is what stops a replay.
        $refused = $this->refusal(static fn () => Webhooks::verify($body, $signature, $timestamp, self::SECRET));

        self::assertSame('timestamp_outside_tolerance', $refused->reason());

        $event = Webhooks::verify($body, $signature, $timestamp, self::SECRET, tolerance: 0);

        self::assertSame('user.updated', $event['event']);
    }

    public function testRefusesATimestampThatIsNotAnInstant(): void
    {
        $body = '{"id":1,"event":"user.updated","occurred_at":"2026-03-02T09:00:00+00:00","data":{}}';
        $timestamp = 'the second tuesday';

        $refused = $this->refusal(fn () => Webhooks::verify(
            $body,
            $this->sign($body, $timestamp),
            $timestamp,
            self::SECRET,
        ));

        self::assertSame('timestamp_unreadable', $refused->reason());
    }

    public function testRefusesABodyThatIsSignedButNotAnEvent(): void
    {
        $body = 'not json at all';
        $timestamp = $this->now();

        $refused = $this->refusal(fn () => Webhooks::verify(
            $body,
            $this->sign($body, $timestamp),
            $timestamp,
            self::SECRET,
        ));

        self::assertSame('body_unparseable', $refused->reason());
    }
}
