<?php

declare(strict_types=1);

/**
 * Refresh openapi/company-v3.json from the deployment that implements it.
 *
 * `--check` writes nothing and exits 1 on a difference, which is what CI runs nightly.
 */

const TARGET = 'openapi/company-v3.json';

const PUBLISHED = 'https://api.clockster.com/openapi/v3.json';

$check = in_array('--check', $argv, true);
$source = getenv('CLOCKSTER_SPEC_URL') ?: PUBLISHED;

$context = stream_context_create(['http' => ['timeout' => 60, 'header' => "Accept: application/json\r\n"]]);
$current = @file_get_contents($source, context: $context);

if ($current === false) {
    fwrite(STDERR, $source . ' cannot be read.' . PHP_EOL);

    exit(1);
}

$published = json_decode($current, true, flags: JSON_THROW_ON_ERROR);
$committed = is_file(TARGET) ? json_decode((string) file_get_contents(TARGET), true) : null;

// Compared as documents rather than as text: the builder writes four-space indentation and this
// writes two, and that difference is not drift.
if ($committed === $published) {
    echo 'Specification is current.' . PHP_EOL;

    exit(0);
}

if ($check) {
    fwrite(STDERR, sprintf('The specification has drifted from %s. Run `composer spec generate`.%s', $source, PHP_EOL));

    exit(1);
}

if (!is_dir(dirname(TARGET))) {
    mkdir(dirname(TARGET), 0o755, true);
}

$written = json_encode(
    $published,
    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
);

// PHP indents with four spaces; the other clients commit two. Each repository keeps its own copy,
// and the comparison above is on the document rather than on the bytes.
file_put_contents(TARGET, $written . "\n");

echo 'Specification updated. Run `composer generate`.' . PHP_EOL;
