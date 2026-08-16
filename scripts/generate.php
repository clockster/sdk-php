<?php

declare(strict_types=1);

/**
 * Generate src/Generated from openapi/company-v3.json.
 *
 * The output is committed, so an API change appears in review as the lines of the client it moves.
 *
 * Three kinds of file come out of it. Shapes.php names every shape the document describes as a
 * PHPStan array shape — the components it names, and one per request body and answer besides.
 * Api.php holds the client's namespace properties and the operations that hang off the root. One
 * file per namespace holds its operations: `$clockster->users->list(...)`, and a Generator beside
 * each listing that pages on a cursor.
 *
 * Nothing here validates. An answer is the JSON as it arrived; the shapes are documentation a
 * static analyser reads and the interpreter never sees, so a field the API adds tomorrow reaches
 * the caller today rather than being refused on the way in.
 */

const SPEC = 'openapi/company-v3.json';

const OUT = 'src/Generated';

const PREFIX = 'company.v3.';

const WIDTH = 100;

/**
 * Resource-controller verbs of the operation ids, mapped to the vocabulary of the SDK. The same
 * table the other three clients use, so one operation is called one thing in all of them.
 */
const VERBS = [
    'index' => 'list',
    'show' => 'get',
    'store' => 'create',
    'destroy' => 'delete',
];

/** Operations whose summary names a verb the map cannot reach, and two listings without an `index`. */
const OVERRIDES = [
    'company.v3.attendance.store' => ['attendance', 'record'],
    'company.v3.files.store' => ['files', 'upload'],
    'company.v3.webhooks.secret' => ['webhooks', 'rotate_secret'],
    'company.v3.payroll.payslips' => ['payroll', 'payslips', 'list'],
    'company.v3.webhooks.events' => ['webhooks', 'events', 'list'],
];

const SCALARS = [
    'string' => 'string',
    'integer' => 'int',
    'number' => 'float',
    'boolean' => 'bool',
];

function fail(string $message): never
{
    fwrite(STDERR, $message . PHP_EOL);

    exit(1);
}

/** A Go-style name for a type: `user-filters` and `list` become `UserFiltersList`. */
function pascal(string $name): string
{
    $parts = preg_split('/[^a-zA-Z0-9]+/', $name, -1, PREG_SPLIT_NO_EMPTY) ?: [];

    return implode('', array_map(static fn (string $part): string => ucfirst(strtolower($part)), $parts));
}

/** A PHP name for a method or an argument: `per_page` becomes `perPage`. */
function camel(string $name): string
{
    return lcfirst(pascal($name));
}

/** What one row of a listing is called: `Data` is a list of `Row`, `Shifts` a list of `Shift`. */
function singular(string $name): string
{
    if (str_ends_with($name, 'Data')) {
        return substr($name, 0, -4) . 'Row';
    }

    if (str_ends_with($name, 'ies')) {
        return substr($name, 0, -3) . 'y';
    }

    if (str_ends_with($name, 'ss') || !str_ends_with($name, 's')) {
        return $name . 'Item';
    }

    return substr($name, 0, -1);
}

/** `UsersListResponse` plus `data` is `UsersListData`, minus the noise. */
function childHint(string $parent, string $key): string
{
    foreach (['Response', 'Body', 'Form'] as $suffix) {
        if (str_ends_with($parent, $suffix)) {
            $parent = substr($parent, 0, -strlen($suffix));

            break;
        }
    }

    return $parent . pascal($key);
}

/**
 * Prose from the document as the lines of a docblock, wrapped to the width the rest of this
 * repository is written to.
 *
 * @return list<string>
 */
function prose(string $text, string $indent): array
{
    $lines = [];
    $fenced = false;

    foreach (explode("\n", rtrim($text)) as $line) {
        if (str_starts_with(trim($line), '```')) {
            $fenced = !$fenced;

            continue;
        }

        if (trim($line) === '') {
            $lines[] = $indent . ' *';

            continue;
        }

        if ($fenced || str_contains($line, '|')) {
            $lines[] = $indent . ' * ' . $line;

            continue;
        }

        foreach (explode("\n", wordwrap($line, WIDTH - strlen($indent) - 3)) as $wrapped) {
            $lines[] = $indent . ' * ' . $wrapped;
        }
    }

    return $lines;
}

/**
 * Every shape the document implies, named once and declared in the order they were needed.
 */
final class Shapes
{
    /** @var array<string, string> */
    private array $blocks = [];

    /** @var array<string, true> */
    private array $taken = [];

    /** @var array<string, string> */
    private array $seen = [];

    /** @var array<string, true> */
    private array $components = [];

    /** @param array<string, mixed> $document */
    public function __construct(private readonly array $document)
    {
    }

    /** @return list<string> the names, in the order they were declared */
    public function names(): array
    {
        return array_keys($this->blocks);
    }

    /** @return array<string, string> */
    public function blocks(): array
    {
        return $this->blocks;
    }

    /**
     * The type of a schema, as a static analyser reads it.
     *
     * @param array<string, mixed>|null $schema
     */
    public function type(?array $schema, string $hint): string
    {
        if ($schema === null) {
            return 'mixed';
        }

        if (isset($schema['$ref']) && is_string($schema['$ref'])) {
            return $this->component($schema['$ref']);
        }

        if (isset($schema['oneOf']) && is_array($schema['oneOf'])) {
            $variants = [];

            foreach ($schema['oneOf'] as $one) {
                $variants[] = $this->type(is_array($one) ? $one : null, $hint . 'Variant');
            }

            return implode('|', $variants);
        }

        $declared = $schema['type'] ?? null;
        $types = is_array($declared) ? $declared : ($declared === null ? [] : [$declared]);
        $nullable = in_array('null', $types, true);
        $rest = array_values(array_filter($types, static fn ($one): bool => $one !== 'null'));

        if ($rest === []) {
            return 'mixed';
        }

        if (count($rest) > 1) {
            // Neither type is the value's, so the caller reads the JSON as it arrived.
            return 'mixed';
        }

        $written = $this->one($schema, (string) $rest[0], $hint);

        return $nullable ? $written . '|null' : $written;
    }

    /** @param array<string, mixed> $schema */
    private function one(array $schema, string $declared, string $hint): string
    {
        if ($declared === 'array') {
            $items = isset($schema['items']) && is_array($schema['items']) ? $schema['items'] : null;

            return 'list<' . $this->type($items, singular($hint)) . '>';
        }

        if ($declared === 'object') {
            return $this->object($schema, $hint);
        }

        return SCALARS[$declared] ?? 'mixed';
    }

    /** @param array<string, mixed> $schema */
    private function object(array $schema, string $hint): string
    {
        if (isset($schema['properties']) && is_array($schema['properties']) && $schema['properties'] !== []) {
            return $this->register($hint, $schema);
        }

        // A map: its keys are values rather than field names, and the document says so.
        $extra = $schema['additionalProperties'] ?? null;

        if (is_array($extra)) {
            return 'array<string, ' . $this->type($extra, $hint . 'Value') . '>';
        }

        return 'array<string, mixed>';
    }

    private function component(string $ref): string
    {
        $name = substr($ref, (int) strrpos($ref, '/') + 1);

        if (isset($this->components[$name])) {
            return $name;
        }

        $this->components[$name] = true;
        $this->taken[$name] = true;

        $schemas = $this->document['components']['schemas'] ?? [];
        $schema = is_array($schemas) && is_array($schemas[$name] ?? null) ? $schemas[$name] : [];

        // Reserved before the body is built: a component that reaches itself would otherwise recur
        // forever.
        $this->blocks[$name] = '';
        $this->blocks[$name] = $this->shape($name, $schema);

        return $name;
    }

    /**
     * Named by the name it wants rather than by its keys alone: a listing's `location` and its
     * `locations` are one shape under one name, where a `position` shaped exactly like a
     * `department` is its own. Shapes the whole surface shares are components in the document, and
     * keep the names it gives them.
     *
     * @param array<string, mixed> $schema
     */
    private function register(string $hint, array $schema): string
    {
        $signature = $hint . "\0" . $this->signature($schema);

        if (isset($this->seen[$signature])) {
            return $this->seen[$signature];
        }

        $name = $hint;

        for ($attempt = 2; isset($this->taken[$name]); $attempt++) {
            $name = $hint . $attempt;
        }

        $this->taken[$name] = true;
        $this->seen[$signature] = $name;

        $this->blocks[$name] = '';
        $this->blocks[$name] = $this->shape($name, $schema);

        return $name;
    }

    /** @param array<string, mixed> $schema */
    private function signature(array $schema): string
    {
        $canonical = $schema;
        $this->sort($canonical);

        return (string) json_encode($canonical);
    }

    /** @param array<string, mixed> $value */
    private function sort(array &$value): void
    {
        ksort($value);

        foreach ($value as &$held) {
            if (is_array($held)) {
                $this->sort($held);
            }
        }
    }

    /** @param array<string, mixed> $schema */
    private function shape(string $name, array $schema): string
    {
        $properties = $schema['properties'] ?? null;

        if (!is_array($properties) || $properties === []) {
            return sprintf('@phpstan-type %s array<string, mixed>', $name);
        }

        $required = is_array($schema['required'] ?? null) ? $schema['required'] : [];
        $fields = [];

        foreach ($properties as $key => $property) {
            $written = $this->type(is_array($property) ? $property : null, childHint($name, (string) $key));
            // An optional key is absent rather than null: an `include` relation is not there
            // unless it was asked for, and a field left out of a write keeps whatever is stored.
            $fields[] = sprintf(
                '%s%s: %s',
                $key,
                in_array($key, $required, true) ? '' : '?',
                $written,
            );
        }

        return sprintf("@phpstan-type %s array{\n *     %s,\n * }", $name, implode(",\n *     ", $fields));
    }
}

/**
 * One route, and everything the client needs to call it.
 */
final class Endpoint
{
    /** @var list<string> */
    public array $segments;

    public string $namespace = '';

    public string $name;

    public string $stem;

    /** @var list<array<string, mixed>> */
    public array $pathParams = [];

    /** @var list<array<string, mixed>> */
    public array $query = [];

    public bool $idempotent = false;

    public ?string $bodyType = null;

    public bool $upload = false;

    /** @var list<string> */
    public array $uploadFields = [];

    public string $returns = 'array<string, mixed>';

    public ?string $rowType = null;

    /** @param array<string, mixed> $spec */
    public function __construct(
        public readonly string $path,
        public readonly string $method,
        public readonly array $spec,
    ) {
        $id = (string) $spec['operationId'];
        $segments = OVERRIDES[$id] ?? null;

        if ($segments === null) {
            $segments = explode('.', substr($id, strlen(PREFIX)));
            $last = count($segments) - 1;
            $segments[$last] = VERBS[$segments[$last]] ?? $segments[$last];
        }

        $this->segments = array_values($segments);
        $this->name = camel((string) end($segments));
        $this->stem = pascal(implode('_', $segments));

        if (count($segments) > 1) {
            $this->namespace = pascal(implode('_', array_slice($segments, 0, -1)));
        }

        foreach ($spec['parameters'] ?? [] as $parameter) {
            if (!is_array($parameter)) {
                continue;
            }

            match ($parameter['in'] ?? '') {
                'path' => $this->pathParams[] = $parameter,
                'query' => $this->query[] = $parameter,
                'header' => $this->idempotent = $this->idempotent || ($parameter['name'] ?? '') === 'Idempotency-Key',
                default => null,
            };
        }
    }

    /** The chain of namespace property names, outermost first. */
    public function group(): array
    {
        return array_map(static fn (string $part): string => camel($part), array_slice($this->segments, 0, -1));
    }

    public function types(Shapes $shapes): void
    {
        $body = $this->spec['requestBody']['content'] ?? [];

        if (is_array($body) && isset($body['application/json']['schema'])) {
            $this->bodyType = $shapes->type($body['application/json']['schema'], $this->stem . 'Body');
        }

        if (is_array($body) && isset($body['multipart/form-data']['schema']['properties'])) {
            $this->upload = true;
            $this->uploadFields = array_values(array_filter(
                array_keys($body['multipart/form-data']['schema']['properties']),
                static fn ($key): bool => $key !== 'file',
            ));
        }

        $status = isset($this->spec['responses']['201']) ? '201' : '200';
        $answer = $this->spec['responses'][$status]['content']['application/json']['schema'] ?? null;

        if (is_array($answer)) {
            $this->returns = $shapes->type($answer, $this->stem . 'Response');
            $this->findCursor($shapes, $answer);
        }
    }

    /** Works out whether this listing pages on a cursor, and what one row of it is. */
    private function findCursor(Shapes $shapes, array $answer): void
    {
        $asked = array_column($this->query, 'name');

        if (!in_array('cursor', $asked, true)) {
            return;
        }

        $data = $answer['properties']['data'] ?? null;
        $meta = $answer['properties']['meta'] ?? null;

        if (!is_array($data) || !is_array($meta)) {
            return;
        }

        $rows = $shapes->type($data, childHint($this->stem . 'Response', 'data'));

        if (!str_starts_with($rows, 'list<')) {
            return;
        }

        $this->rowType = substr($rows, 5, -1);
    }
}

require __DIR__ . '/emit.php';

$raw = file_get_contents(SPEC);

if ($raw === false) {
    fail(SPEC . ' cannot be read.');
}

/** @var array<string, mixed> $document */
$document = json_decode($raw, true, flags: JSON_THROW_ON_ERROR);

$endpoints = [];

foreach ($document['paths'] as $path => $item) {
    foreach ($item as $method => $spec) {
        $endpoints[] = new Endpoint((string) $path, strtoupper((string) $method), $spec);
    }
}

// Sorted by where the method lands rather than by path, so the files read as the client does and
// two runs write the same bytes.
usort($endpoints, static fn (Endpoint $left, Endpoint $right): int
    => [$left->namespace, $left->name] <=> [$right->namespace, $right->name]);

$shapes = new Shapes($document);

foreach ($endpoints as $endpoint) {
    $endpoint->types($shapes);
}

emit($endpoints, $shapes);

printf("%d operations, %d shapes.\n", count($endpoints), count($shapes->blocks()));
