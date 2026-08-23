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

Data from anywhere else has a fourth idea, and it is the empty string. A blank cell in a CSV, an
untouched input, a column somebody's export leaves empty: each of them means "nothing here" rather
than "store nothing here". Sent as it is, it overwrites a name somebody typed into the web
application with nothing, and there is no undoing that. `Write::filled()` is the one line that keeps
the difference:

```php
use Clockster\Write;

$clockster->users->upsert(['users' => [Write::filled([
    'external_id' => $row['external_id'],
    'first_name' => $row['first_name'],
    'role' => UsersRole::EMPLOYEE,
    'location_id' => $locations[$row['location_code']],
    'email' => $row['email'],       // blank in the file, so not sent, so not overwritten
    'position_id' => null,          // null is kept: clearing is a thing you may mean
])]]);
```

It drops the keys holding an empty string and nothing else — `null`, `0`, `false` and `[]` are all
values and all stay. Nested rows are walked, so one call covers a person, a batch of them, or a
whole body.

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

## Sets of values

Where a field takes one of a fixed set, the set has a name and a class of constants under
`Clockster\Generated\Enum`:

```php
use Clockster\Generated\Enum\UsersInclude;
use Clockster\Generated\Enum\UsersRole;
use Clockster\Generated\Enum\UsersStatus;

$clockster->users->upsert(['users' => [['role' => UsersRole::EMPLOYEE, /* … */]]]);
$clockster->users->list(status: UsersStatus::ACTIVE, include: [UsersInclude::LOCATION]);
```

Constants rather than native enums, so one goes wherever the string goes: `UsersRole::EMPLOYEE`
**is** `'employee'`, and a static analyser reads the two as one value. `UsersRole::values()` answers
the lot, in the order the document names them, which is what a dropdown or a check against a file
wants.

Every set is on something you send, and none is in an answer. That is deliberate on the API's part
and it is why naming them costs nothing: a `status` we start answering with next year reaches your
code as the string it is, where a closed type would have refused it. So write against the set and
read whatever arrives.

## Reading a body before it goes

Off by default. A client built with `validate: true` reads a body against the document first and
refuses one it says is wrong, without sending it:

```php
use Clockster\Exception\InvalidBodyException;

$clockster = new Client(getenv('CLOCKSTER_TOKEN'), validate: true);

try {
    $clockster->users->upsert(['users' => $people]);
} catch (InvalidBodyException $refused) {
    // ['body.users.4.first_nane' => ['is not a field the document names here. It names …']]
    report($refused->errors());
}
```

It catches what a `422` would, in the place the body was written rather than against a batch of a
hundred: a misspelled field, a value outside its set, a required one nobody filled in, a length or a
batch size over the limit, a date that is not a day. `errors()` is shaped exactly as
`ValidationException::errors()` is, so one handler reads a refusal from either side — and an
`InvalidBodyException` means nothing was sent, so there is nothing to undo.

It is off by default on purpose. The document describes the API rather than being it, and a body
this refuses may be one the API would have taken; a refusal this package invented is one nobody can
act on. Turn it on where you develop and in CI, leave it off in production. The shapes above say the
same thing earlier and for free — this is for the run where nobody ran an analyser.

The validator can also be called on its own, against a body nothing is about to send:

```php
Clockster\Validator::check('POST', '/company/v3/users/upsert', ['users' => $people]);
```

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

One refusal is not the API's: an `InvalidBodyException` is this package declining to send a body,
and only where you asked it to look — see above.

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
    validate: true,                         // read a body against the document before sending it
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
— an API change appears in review as the lines of the client it moves. The operations, the shapes,
the sets of values under `Enum` and the rules the validator reads all come out of the one document,
so none of the four can say a different thing from the others. To refresh:

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
