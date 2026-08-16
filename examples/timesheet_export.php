<?php

declare(strict_types=1);

/**
 * Export a month of timesheets as CSV, a row per person per day.
 *
 *     export CLOCKSTER_TOKEN=...
 *     php examples/timesheet_export.php 2026-08 > august.csv
 *
 * What it shows: walking a listing with listAll(), asking for the facts with `include`, and the
 * two things that catch people out — times are seconds, and a day nobody was scheduled for answers
 * a null `planned` rather than being left out.
 */

use Clockster\Client;

require __DIR__ . '/../vendor/autoload.php';

/** @var list<string> $argv */
$argv = $_SERVER['argv'];

if (count($argv) !== 2) {
    fwrite(STDERR, sprintf("usage: %s 2026-08\n", $argv[0]));

    exit(2);
}

$token = getenv('CLOCKSTER_TOKEN');

if (!is_string($token) || $token === '') {
    fwrite(STDERR, "Set CLOCKSTER_TOKEN to a company API key (Settings, API).\n");

    exit(2);
}

$month = DateTimeImmutable::createFromFormat('!Y-m', $argv[1]);

if ($month === false) {
    fwrite(STDERR, sprintf("%s is not a month, which is written 2026-08.\n", $argv[1]));

    exit(2);
}

$baseUrl = getenv('CLOCKSTER_BASE_URL');
$clockster = new Client($token, baseUrl: is_string($baseUrl) && $baseUrl !== '' ? $baseUrl : null);

$out = fopen('php://output', 'w');

if ($out === false) {
    exit(1);
}

fputcsv($out, [
    'date',
    'external_id',
    'name',
    'planned_start',
    'planned_end',
    'planned_seconds',
    'worked_seconds',
    'late_seconds',
    'overworked_seconds',
], escape: '');

// The whole month in one walk: the cursor is the iterator's business, the filters are ours.
foreach ($clockster->timesheets->listAll(
    dateFrom: $month->format('Y-m-01'),
    dateTo: $month->format('Y-m-t'),
    include: ['actual', 'variance'],
) as $row) {
    $user = $row['user'];
    $planned = $row['planned'] ?? null;
    $actual = $row['actual'] ?? null;
    $variance = $row['variance'] ?? null;

    fputcsv($out, [
        $row['date'],
        $user['external_id'] ?? '',
        trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')),
        // A day nobody was scheduled for answers null here rather than being left out of the
        // listing, and a day nobody clocked in on answers null for the actual.
        $planned['start'] ?? '',
        $planned['end'] ?? '',
        $planned['time_planned'] ?? '',
        $actual['time_worked'] ?? '',
        $variance['time_late'] ?? '',
        $variance['time_overworked'] ?? '',
    ], escape: '');
}

fclose($out);
