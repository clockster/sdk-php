<?php

declare(strict_types=1);

namespace Clockster\Tests;

use Clockster\Client;
use Clockster\Exception\ApiException;
use Clockster\Exception\ClocksterException;
use Clockster\Exception\NotFoundException;
use Clockster\Exception\RateLimitException;
use Clockster\Exception\ServerException;
use Clockster\Exception\TransportException;
use Clockster\Exception\ValidationException;
use Clockster\Http\Response;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * What the client does with a call, against a stub rather than the API.
 */
#[CoversNothing]
final class ClientTest extends TestCase
{
    private function client(RecordingTransport $transport): Client
    {
        return new Client('token', transport: $transport);
    }

    public function testAnswersTheParsedBody(): void
    {
        $transport = RecordingTransport::answering('{"data":[{"id":7,"first_name":"Aisulu"}]}');

        $answer = $this->client($transport)->users->list();

        self::assertSame([['id' => 7, 'first_name' => 'Aisulu']], $answer['data']);
    }

    public function testCarriesTheTokenOnEveryCall(): void
    {
        $transport = RecordingTransport::answering('{"data":[]}');

        $this->client($transport)->users->list();

        $headers = $transport->first()->headers;

        self::assertSame('Bearer token', $headers['Authorization']);
        self::assertSame('application/json', $headers['Accept']);
        // So the request log says which client made a call rather than which HTTP library did.
        self::assertSame('clockster-php/' . Client::VERSION, $headers['User-Agent']);
    }

    public function testAnIntegrationCanNameItselfInstead(): void
    {
        $transport = RecordingTransport::answering('{"data":[]}');

        (new Client('token', userAgent: 'acme-hr/1.4', transport: $transport))->users->list();

        self::assertSame('acme-hr/1.4', $transport->first()->headers['User-Agent']);
    }

    public function testWritesAListParameterCommaSeparated(): void
    {
        $transport = RecordingTransport::answering('{"data":[]}');

        $this->client($transport)->users->list(include: ['location', 'department'], ids: [1, 2]);

        // Both forms are accepted by the API; this is the one the document describes, and the one
        // that keeps the parameter called `include` rather than `include[]`.
        self::assertSame('location,department', $transport->query()['include']);
        self::assertSame('1,2', $transport->query()['ids']);
    }

    public function testLeavesOutWhatWasNotAskedFor(): void
    {
        $transport = RecordingTransport::answering('{"data":[]}');

        $this->client($transport)->users->list(perPage: 50);

        // An omitted filter and an empty one mean different things, and an argument left alone is
        // the first.
        self::assertSame(['per_page' => '50'], $transport->query());
    }

    public function testWritesAParameterTheOperationRequires(): void
    {
        $transport = RecordingTransport::answering('{"data":[]}');

        $this->client($transport)->attendance->list(dateFrom: '2026-08-01', dateTo: '2026-08-31');

        self::assertSame('2026-08-01', $transport->query()['date_from']);
        self::assertSame('2026-08-31', $transport->query()['date_to']);
    }

    public function testPathParametersLandInThePath(): void
    {
        $transport = RecordingTransport::answering('{"data":{"id":42}}');

        $this->client($transport)->users->get(42);

        self::assertStringEndsWith('/company/v3/users/42', $transport->first()->url);
    }

    public function testSendsAnIdempotencyKeyWhenAsked(): void
    {
        $transport = RecordingTransport::answering('{"data":{"id":1}}', 201);

        $this->client($transport)->webhooks->create(
            ['url' => 'https://example.test/hook', 'events' => ['user.updated'], 'active' => true],
            idempotencyKey: '2f8a',
        );

        self::assertSame('2f8a', $transport->first()->headers['Idempotency-Key']);
    }

    public function testAKeyLeftOutOfAWriteIsNotSent(): void
    {
        $transport = RecordingTransport::answering('{"data":[]}');

        $this->client($transport)->users->upsert(['users' => [[
            'external_id' => 'HR-1',
            'first_name' => 'Aisulu',
            'role' => 'employee',
            'location_id' => 3,
            'position_id' => null,
        ]]]);

        $sent = $transport->body();

        self::assertIsArray($sent['users']);
        self::assertIsArray($person = $sent['users'][0]);

        // Nothing was said about the department, so the stored one stays, where the position was
        // cleared on purpose.
        self::assertArrayNotHasKey('department_id', $person);
        self::assertArrayHasKey('position_id', $person);
        self::assertNull($person['position_id']);
        self::assertSame('application/json', $transport->first()->headers['Content-Type']);
    }

    public function testARefusalIsThrown(): void
    {
        $transport = RecordingTransport::answering(
            '{"error":{"code":"validation_failed","message":"The given data was invalid.",'
            . '"request_id":"9f2b","errors":{"users.0.role":["The role is required."]}}}',
            422,
        );

        try {
            $this->client($transport)->users->upsert(['users' => []]);

            self::fail('A refused call answered rather than throwing.');
        } catch (ValidationException $refused) {
            self::assertSame('validation_failed', $refused->code());
            self::assertSame('9f2b', $refused->requestId());
            self::assertSame(['The role is required.'], $refused->errors()['users.0.role']);
            self::assertStringContainsString('request_id 9f2b', $refused->getMessage());
        }
    }

    public function testRateLimitCarriesRetryAfter(): void
    {
        $transport = RecordingTransport::answering(
            '{"error":{"code":"too_many_requests","message":"Slow down.","request_id":"1a"}}',
            429,
            ['retry-after' => '30'],
        );

        try {
            $this->client($transport)->users->list();

            self::fail('The limit was not enforced.');
        } catch (RateLimitException $refused) {
            self::assertSame(30, $refused->retryAfter());
        }
    }

    public function testAnotherCompanysRowIsNotFound(): void
    {
        $transport = RecordingTransport::answering(
            '{"error":{"code":"not_found","message":"No such user.","request_id":"3c"}}',
            404,
        );

        $this->expectException(NotFoundException::class);

        $this->client($transport)->users->get(999);
    }

    public function testAServerErrorIsOurs(): void
    {
        $transport = RecordingTransport::answering('{"error":{"code":"unavailable"}}', 503);

        $this->expectException(ServerException::class);

        $this->client($transport)->users->list();
    }

    public function testARefusalNeedNotBeJson(): void
    {
        // An edge refusal can answer before the application does.
        $transport = RecordingTransport::answering('<html>bad gateway</html>', 502);

        try {
            $this->client($transport)->users->list();

            self::fail('The refusal was swallowed.');
        } catch (ApiException $refused) {
            self::assertSame('unknown', $refused->code());
            self::assertSame(502, $refused->status());
            self::assertStringContainsString('The API answered 502.', $refused->getMessage());
        }
    }

    public function testAnEmptyAnswerIsNotAnError(): void
    {
        $transport = new RecordingTransport(new Response(204, ''));

        self::assertSame([], $this->client($transport)->users->list());
    }

    public function testAnAnswerThatIsNotJsonIsATransportFailure(): void
    {
        $transport = RecordingTransport::answering('<html>a proxy speaking</html>');

        $this->expectException(TransportException::class);

        $this->client($transport)->users->list();
    }

    public function testUploadSendsTheFileAsMultipart(): void
    {
        $transport = RecordingTransport::answering('{"data":{"id":1}}', 201);

        $this->client($transport)->files->upload('%PDF-1.7', 'agreement.pdf', 'agreement');

        $sent = $transport->first();

        self::assertStringStartsWith('multipart/form-data; boundary=', $sent->headers['Content-Type']);
        self::assertStringContainsString('filename="agreement.pdf"', (string) $sent->body);
        self::assertStringContainsString('%PDF-1.7', (string) $sent->body);
        self::assertStringContainsString('name="name"', (string) $sent->body);
    }

    public function testNewNeedsAKey(): void
    {
        $this->expectException(ClocksterException::class);

        new Client('');
    }

    public function testTheBaseUrlLosesItsTrailingSlash(): void
    {
        $transport = RecordingTransport::answering('{"data":[]}');

        (new Client('token', baseUrl: 'https://demo.clockster.com/', transport: $transport))->users->list();

        self::assertStringStartsWith('https://demo.clockster.com/company/v3/users', $transport->first()->url);
    }
}
