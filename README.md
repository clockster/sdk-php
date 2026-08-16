# sdk-php

Official PHP SDK for the [Clockster Company API](https://api.clockster.com/openapi/v3.json).

Server-to-server client for a company's employees, structure, schedules, attendance, tasks and
documents. Generated from the API's OpenAPI document. No dependencies beyond curl and JSON.

```bash
composer require clockster/sdk
```

Requires PHP 8.3 or newer.

## Quickstart

One token authenticates one company. Create it under Settings → API in the web application.

```php
use Clockster\Client;

$clockster = new Client(getenv('CLOCKSTER_TOKEN'));

$me = $clockster->me();

$locations = $clockster->locations->upsert([
    'items' => [['external_id' => 'HQ', 'title' => 'Head office']],
]);

$clockster->users->upsert([
    'users' => [[
        'external_id' => 'HR-1',
        'first_name' => 'Aisulu',
        'role' => 'employee',
        'location_id' => $locations['data'][0]['id'],
    ]],
]);

$timesheets = $clockster->timesheets->list(dateFrom: '2026-08-01', dateTo: '2026-08-31');
```

A method answers the parsed body, so rows are `$answer['data']`. Nothing is validated on the way
in: the answer is the JSON as it arrived, and a field we add tomorrow reaches your code today.

Filters are named arguments, in the order the documentation lists them — pass the ones you want:

```php
$clockster->users->list(perPage: 100, status: 'active', include: ['location', 'department']);
```

The methods are named after the operations, so the API documentation is the reference for both:
`GET /users` is `$clockster->users->list(...)`, `POST /users/upsert` is
`$clockster->users->upsert(...)`. The TypeScript, Python and Go clients use the same names.

## Absent, null and set

A key that was not asked for is absent, never null: `null` means the value is known to be empty,
and an absent key means you did not ask. Arrays say both without ceremony:

```php
$clockster->users->upsert(['users' => [[
    'external_id' => 'HR-1',
    'first_name' => 'Aisulu',
    'role' => 'employee',
    'location_id' => 3,
    'position_id' => null,   // clears the stored position
    // department_id is not there at all, so the stored department stays.
]]]);
```

The same holds when reading: `array_key_exists('department', $user)` asks whether you requested it
with `include`, where `$user['department'] === null` says the employee has none.

## Types

Every request body, query and answer is described by a PHPStan array shape in
`Clockster\Generated\Shapes`, and the generated methods carry them:

```php
/** @return UsersListResponse */
public function list(?int $perPage = null, /* … */): array
```

Run PHPStan or Psalm and a misspelled key or a missing required one is an error before it is a
`422`. Without a static analyser they are documentation, and your editor still reads them for
completion. Nothing is enforced at run time: the shapes describe what the document says, not
something this package confirmed.

## Paging

Thirteen listings page on a cursor, and each has a `…All()` beside it that walks them, yielding one
row at a time:

```php
foreach ($clockster->users->listAll(perPage: 100, include: ['location']) as $user) {
    echo $user['external_id'] ?? $user['id'], PHP_EOL;
}
```

It is a Generator, so a page is fetched only when the loop asks for the next row, and leaving the
loop stops the walk. A refused page is thrown where it was refused, so half a listing is never
mistaken for the whole of one. A cursor belongs to the filters it was issued under: change them and
walk again.

## Refusals

A refusal is thrown, never returned. `code()` is what to branch on: it names the reason and does
not change, where `getMessage()` is prose and may. `requestId()` identifies the call in our logs.

```php
use Clockster\Exception\ApiException;
use Clockster\Exception\RateLimitException;
use Clockster\Exception\ValidationException;

try {
    $clockster->users->upsert(['users' => $people]);
} catch (ValidationException $refused) {
    report($refused->code(), $refused->errors(), $refused->requestId());
} catch (RateLimitException $refused) {
    sleep($refused->retryAfter() ?? 60);
} catch (ApiException $refused) {
    report($refused->status(), $refused->code());
}
```

One class per status worth catching: `AuthenticationException` (401), `ForbiddenException` (403),
`NotFoundException` (404), `ConflictException` (409), `ValidationException` (422),
`RateLimitException` (429) and `ServerException` (5xx). Another company's id answers
`NotFoundException` rather than `ForbiddenException` — you cannot learn that it exists.

A call that got no answer at all is a `TransportException` instead, and it is the one case worth
retrying blind: the request may have been applied.

## Retries and idempotency

Retry a 429 and a 5xx; do not retry a 4xx. A keyed write converges on what you meant rather than
doubling anything, so a timed-out upsert is safe to send again. Four writes have no key of your own
to match a second attempt against — a rota, a webhook endpoint, a rotated secret, a delivery sent
again — and those take a key instead:

```php
$clockster->schedules->create($body, idempotencyKey: $attempt);
```

## Uploading a file

One operation carries bytes rather than JSON:

```php
$stored = $clockster->files->upload(
    file: (string) file_get_contents('agreement.pdf'),
    filename: 'agreement.pdf',
    name: 'agreement',
);
```

## Webhooks

Verifying a delivery is the only way to the event it carries, so there is no path that acts on one
that was not verified:

```php
use Clockster\Exception\WebhookVerificationException;
use Clockster\Webhooks;

try {
    $event = Webhooks::verifyGlobals(getenv('CLOCKSTER_WEBHOOK_SECRET'));
} catch (WebhookVerificationException $refused) {
    http_response_code(400);

    exit;
}

http_response_code(200);
```

In a framework, hand the raw body and the two headers to `Webhooks::verify()` — Laravel's
`$request->getContent()` rather than anything re-encoded, since re-serialising a parsed object does
not reproduce the signed bytes. Answer 2xx quickly and do the work afterwards — a timeout is
retried — and deduplicate on `$event['id']`, since the same event may arrive twice.

## Another HTTP client

Calls go out over curl unless you say otherwise. Anything implementing `Clockster\Http\Transport`
is accepted, and `Psr18Transport` is one for PSR-18 clients:

```php
use Clockster\Http\Psr18Transport;

$clockster = new Client($token, transport: new Psr18Transport(
    $guzzle,          // Psr\Http\Client\ClientInterface
    $requestFactory,  // Psr\Http\Message\RequestFactoryInterface
    $streamFactory,   // Psr\Http\Message\StreamFactoryInterface
));
```

That class is the only thing here that needs anything installed — `psr/http-client` and
`psr/http-factory` — and nothing loads it unless you name it.

## Configuration

```php
$clockster = new Client(
    token: getenv('CLOCKSTER_TOKEN'),
    baseUrl: 'https://demo.clockster.com',  // a demo stand instead of production
    timeout: 60.0,                          // seconds, applied to each request
    userAgent: 'acme-hr/1.4',               // names your integration in our request log
);
```

Requests carry `clockster-php/<version>` unless `userAgent` says otherwise. The token is read per
request, so rotating it does not require a new client.

## Dates, times and numbers

Instants, dates and clock times are strings, in the shapes the document states, rather than
`DateTimeImmutable`: a date carries no zone and a clock time is read in the timezone stated beside
it, and converting either would decide something this package does not know. Durations are seconds.
Decimal amounts are JSON numbers rounded to two places; do not accumulate them in binary floating
point.

## Generated from the document

`src/Generated` is written by `scripts/generate.php` from `openapi/company-v3.json`, and committed
— an API change appears in review as the lines of the client it moves. To refresh:

```bash
composer spec generate check
```

A nightly job compares the committed document with the published one, so drift is noticed here
rather than by you.

## Examples

Two whole integrations live in [examples](examples): a roster sync in, a timesheet export out.

## Versions

This package follows its own semver, unrelated to the version of the API and to the other SDKs. A
new API version would be a major release of this package rather than a second package.

## License

MIT. See [LICENSE](LICENSE).
