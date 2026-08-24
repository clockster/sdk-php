<?php

declare(strict_types=1);

/**
 * Sync a roster from a CSV into Clockster, and dismiss whoever is no longer in it.
 *
 * The shape a real HR integration has: your system knows people by a key of its own, and every
 * sync is "here is everybody, make it so". `external_id` is what makes that idempotent — send it
 * on every person and the second run updates rather than duplicates.
 *
 *     export CLOCKSTER_TOKEN=...
 *     php examples/roster_sync.php people.csv
 *
 * The file wants a header row:
 *
 *     external_id,first_name,last_name,email,phone,location_code,location_title
 *
 * Nothing is deleted. Somebody missing from the file is dismissed, which frees their seat and
 * leaves their attendance, payroll and documents readable.
 */

use Clockster\Client;
use Clockster\Exception\ValidationException;
use Clockster\Generated\Enum\UsersRole;
use Clockster\Generated\Enum\UsersStatus;
use Clockster\Write;

require __DIR__ . '/../vendor/autoload.php';

/** The roster endpoint takes 100 people a call, and says so; the rest is one round trip each. */
const BATCH = 100;

/**
 * @return list<array<string, string>>
 */
function rows(string $path): array
{
    $handle = fopen($path, 'r');

    if ($handle === false) {
        throw new RuntimeException($path . ' cannot be read.');
    }

    $header = fgetcsv($handle, escape: '');

    if (!is_array($header)) {
        throw new RuntimeException($path . ' has no header row.');
    }

    $rows = [];

    while (($record = fgetcsv($handle, escape: '')) !== false) {
        $row = [];

        foreach ($header as $index => $column) {
            $row[trim((string) $column)] = trim((string) ($record[$index] ?? ''));
        }

        $rows[] = $row;
    }

    fclose($handle);

    return $rows;
}

/**
 * Write the locations the file names, and read their ids back out of the answer.
 *
 * An employee is filed against a location by id, and the file knows only its own code — so the
 * codes go up as `external_id` and come back beside the id they were given.
 *
 * @param list<array<string, string>> $source
 *
 * @return array<string, int>
 */
function locationsByCode(Client $clockster, array $source): array
{
    $named = [];

    foreach ($source as $row) {
        if (($row['location_code'] ?? '') !== '') {
            $named[$row['location_code']] = $row['location_title'] ?? $row['location_code'];
        }
    }

    if ($named === []) {
        return [];
    }

    ksort($named);

    $items = [];

    foreach ($named as $code => $title) {
        $items[] = ['external_id' => (string) $code, 'title' => $title];
    }

    $answer = $clockster->locations->upsert(['items' => $items]);
    $found = [];

    foreach ($answer['data'] as $outcome) {
        if ($outcome['external_id'] !== null) {
            $found[$outcome['external_id']] = $outcome['id'];
        }
    }

    return $found;
}

/**
 * @param array<string, string> $row
 * @param array<string, int>    $locations
 *
 * @return array{external_id: string, first_name: string, role: 'employee', location_id: int,
 *               last_name?: string, email?: string, phone?: string}
 */
function person(array $row, array $locations): array
{
    // A column the file leaves blank is a column it has nothing to say about, and an empty string
    // says something else: it would blank a name somebody typed into the web application.
    // Write::filled() keeps the two apart — a blank cell is not sent, so the stored value stays.
    return Write::filled([
        'external_id' => $row['external_id'],
        'first_name' => $row['first_name'],
        'role' => UsersRole::EMPLOYEE,
        'location_id' => $locations[$row['location_code'] ?? ''] ?? 0,
        'last_name' => $row['last_name'] ?? '',
        'email' => $row['email'] ?? '',
        'phone' => $row['phone'] ?? '',
    ]);
}

/**
 * Everyone on file here, not in the roster, and not already gone.
 *
 * @param array<string, true> $keys
 *
 * @return list<array{external_id: string}>
 */
function dismissed(Client $clockster, array $keys): array
{
    $leaving = [];

    foreach ($clockster->users->listAll(perPage: BATCH, status: UsersStatus::ACTIVE) as $employee) {
        $externalId = $employee['external_id'];

        if ($externalId !== null && !isset($keys[$externalId])) {
            $leaving[] = ['external_id' => $externalId];
        }
    }

    return $leaving;
}

/** @var list<string> $argv */
$argv = $_SERVER['argv'];

if (count($argv) !== 2) {
    fwrite(STDERR, sprintf("usage: %s people.csv\n", $argv[0]));

    exit(2);
}

$token = getenv('CLOCKSTER_TOKEN');

if (!is_string($token) || $token === '') {
    fwrite(STDERR, "Set CLOCKSTER_TOKEN to a company API key (Settings, API).\n");

    exit(2);
}

$source = rows($argv[1]);

// A demo stand answers the same API; production is the default.
$baseUrl = getenv('CLOCKSTER_BASE_URL');
$clockster = new Client($token, baseUrl: is_string($baseUrl) && $baseUrl !== '' ? $baseUrl : null);

$company = $clockster->me();

printf("Syncing %d people into %s.\n", count($source), $company['data']['title']);

$locations = locationsByCode($clockster, $source);
$people = array_map(static fn (array $row): array => person($row, $locations), $source);
$written = 0;

try {
    foreach (array_chunk($people, BATCH) as $batch) {
        $answer = $clockster->users->upsert(['users' => $batch]);
        $written += count($answer['data']);

        printf("  %d/%d\n", $written, count($people));
    }
} catch (ValidationException $refused) {
    // None of the batch landed: the write is all or nothing, so the file is fixable and the whole
    // run can be repeated.
    fwrite(STDERR, sprintf("Refused: %s %s\n", $refused->code(), json_encode($refused->errors())));

    exit(1);
}

$keys = [];

foreach ($source as $row) {
    $keys[$row['external_id']] = true;
}

$leaving = dismissed($clockster, $keys);

if ($leaving !== []) {
    $clockster->users->dismiss(['users' => $leaving]);
}

printf("%d written, %d dismissed.\n", $written, count($leaving));
