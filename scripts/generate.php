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
 * Beside them, one class of constants per closed set of values the document names — every one of
 * which is on something you send rather than in an answer, so naming them takes nothing away.
 *
 * Nothing here validates. An answer is the JSON as it arrived; the shapes are documentation a
 * static analyser reads and the interpreter never sees, so a field the API adds tomorrow reaches
 * the caller today rather than being refused on the way in. What a caller sends can be written
 * against the document instead, from Constraints.php, and only where it asks for that.
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

/**
 * The singular a field is named for, so a filter and the field it filters read as one thing:
 * `statuses` and `status` are one set, and so are `types` and `type`.
 */
function singularField(string $name): string
{
    if (str_ends_with($name, 'ses')) {
        return substr($name, 0, -2);
    }

    if (str_ends_with($name, 'ss') || str_ends_with($name, 'us') || !str_ends_with($name, 's')) {
        return $name;
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
 * The closed sets of values, as the document names them.
 *
 * The name is read rather than worked out. A generator deriving one needs the rule for it, and the
 * four clients would each need the same rule, in the same shape, kept in step forever — so the
 * document carries it instead, as `x-clockster-set` beside every enum. What is left here is the
 * collecting: which names there are, what each holds, and where a caller sends one.
 *
 * Only what you send is ever closed. Every enum in the document is on a query parameter or in a
 * request body and none is in an answer, so naming them costs nothing on the way back: a status the
 * API starts answering with tomorrow is still just a string to this client.
 *
 * A set of numbers is named by the document too and left unnamed here. `0|1` is shorter than any
 * name for it, and a constant called VALUE_0 tells a reader nothing the value did not.
 */
final class Enums
{
    /** @var array<string, array{word: string, values: list<string>, sites: list<string>}> */
    private array $classes = [];

    /**
     * @param list<Endpoint>       $endpoints
     * @param array<string, mixed> $document
     */
    public function __construct(array $endpoints, private readonly array $document)
    {
        foreach ($endpoints as $endpoint) {
            $this->collect($endpoint);
        }

        foreach ($this->classes as $name => $set) {
            sort($set['sites']);
            $this->classes[$name] = $set;
        }

        ksort($this->classes);
    }

    /**
     * The classes to write, by name.
     *
     * @return array<string, array{word: string, values: list<string>, sites: list<string>}>
     */
    public function classes(): array
    {
        return $this->classes;
    }

    /**
     * How a set is written where a caller meets it, and null where the schema holds no set.
     *
     * Under its name where the document gives one and the values are words; written out in full
     * otherwise, which is what a set of numbers gets.
     *
     * @param array<string, mixed> $schema
     */
    public static function reading(array $schema): ?string
    {
        $values = self::scalars($schema);

        if ($values === null) {
            return null;
        }

        $name = $schema['x-clockster-set'] ?? null;

        return is_string($name) && self::named($values) ? $name : self::union($values);
    }

    /**
     * A set written out in full, the way a static analyser reads one.
     *
     * @param list<string|int|float> $values
     */
    public static function union(array $values): string
    {
        return implode('|', array_map(
            static fn (string|int|float $value): string => is_string($value)
                ? "'" . str_replace("'", "\\'", $value) . "'"
                : var_export($value, true),
            $values,
        ));
    }

    /**
     * The values of a schema where it holds a set the document states coherently, and null where it
     * does not.
     *
     * Coherent means the values are all of the type declared beside them. A document saying
     * `{"type": "integer", "enum": ["0", "1"]}` disagrees with itself, and a client that picks a
     * side bakes the disagreement into everybody's static analysis.
     *
     * @param array<string, mixed> $schema
     *
     * @return list<string|int|float>|null
     */
    public static function scalars(array $schema): ?array
    {
        if (!isset($schema['enum']) || !is_array($schema['enum']) || $schema['enum'] === []) {
            return null;
        }

        $values = array_values($schema['enum']);
        $declared = $schema['type'] ?? null;
        $types = is_array($declared) ? $declared : [$declared];
        $held = ['string' => 'is_string', 'integer' => 'is_int', 'number' => 'is_float'];

        foreach ($held as $type => $test) {
            if (!in_array($type, $types, true)) {
                continue;
            }

            $matching = array_values(array_filter($values, $test));

            // `number` covers a whole number written without a point, which JSON reads as an int.
            if ($type === 'number' && count($matching) !== count($values)) {
                $matching = array_values(array_filter($values, 'is_numeric'));
            }

            return count($matching) === count($values) ? $matching : null;
        }

        return null;
    }

    /**
     * Whether a set is one a class of constants can be built from. A name needs words.
     *
     * @param list<string|int|float> $values
     */
    public static function named(array $values): bool
    {
        return count(array_filter($values, 'is_string')) === count($values);
    }

    private function collect(Endpoint $endpoint): void
    {
        $reached = array_merge($endpoint->group(), [$endpoint->name]);
        $site = '$clockster->' . implode('->', $reached) . '()';

        foreach ($endpoint->query as $parameter) {
            $schema = $parameter['schema'] ?? null;

            $this->walk(
                is_array($schema) ? $schema : null,
                'a filter on `' . $site . '`',
                (string) $parameter['name'],
                [],
            );
        }

        $body = $endpoint->spec['requestBody']['content']['application/json']['schema'] ?? null;

        $this->walk(is_array($body) ? $body : null, 'a `' . $site . '` body', '', []);
    }

    /**
     * @param array<string, mixed>|null $schema
     * @param list<string>              $seen   the components already entered, so one reaching
     *                                          itself stops rather than recurring forever
     */
    private function walk(?array $schema, string $site, string $word, array $seen): void
    {
        if ($schema === null) {
            return;
        }

        if (isset($schema['$ref']) && is_string($schema['$ref'])) {
            $name = substr($schema['$ref'], (int) strrpos($schema['$ref'], '/') + 1);

            if (in_array($name, $seen, true)) {
                return;
            }

            $schemas = $this->document['components']['schemas'] ?? [];
            $held = is_array($schemas) && is_array($schemas[$name] ?? null) ? $schemas[$name] : null;

            $this->walk($held, $site, $word, array_merge($seen, [$name]));

            return;
        }

        if ($word !== '' && isset($schema['enum'])) {
            $this->remember($site, $word, $schema);
        }

        foreach (['items', 'additionalProperties'] as $key) {
            if (isset($schema[$key]) && is_array($schema[$key])) {
                $this->walk($schema[$key], $site, $word, $seen);
            }
        }

        foreach (['oneOf', 'anyOf', 'allOf'] as $key) {
            foreach ($schema[$key] ?? [] as $one) {
                if (is_array($one)) {
                    $this->walk($one, $site, $word, $seen);
                }
            }
        }

        foreach ($schema['properties'] ?? [] as $key => $property) {
            if (is_array($property)) {
                $this->walk($property, $site, (string) $key, $seen);
            }
        }
    }

    /** @param array<string, mixed> $schema */
    private function remember(string $site, string $word, array $schema): void
    {
        $values = self::scalars($schema);
        $name = $schema['x-clockster-set'] ?? null;

        if ($values === null) {
            fwrite(STDERR, sprintf(
                "Left as a bare type: %s names %s, which is not the type declared beside it.\n",
                $word,
                (string) json_encode($schema['enum']),
            ));

            return;
        }

        // Named by the document and left unnamed here: a set of numbers is written out where it is
        // used. A document that names no set at all is one built before this was published.
        if (!is_string($name) || !self::named($values)) {
            return;
        }

        /** @var list<string> $values */
        if (!isset($this->classes[$name])) {
            $this->classes[$name] = ['word' => singularField($word), 'values' => $values, 'sites' => [$site]];

            return;
        }

        // One name over two different sets would publish constants for one of them and check
        // against the other. The document has a test against this; so does this.
        if ($this->classes[$name]['values'] !== $values) {
            fail(sprintf(
                '%s names two different sets: %s and %s.',
                $name,
                (string) json_encode($this->classes[$name]['values']),
                (string) json_encode($values),
            ));
        }

        if (!in_array($site, $this->classes[$name]['sites'], true)) {
            $this->classes[$name]['sites'][] = $site;
        }
    }
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

    /**
     * The sets are declared before anything else, so a shape can never take a name one of them
     * holds, and every file naming one imports it the way it imports a shape.
     *
     * @param array<string, mixed> $document
     */
    public function __construct(private readonly array $document, private readonly Enums $enums)
    {
        foreach ($this->enums->classes() as $name => $set) {
            $this->taken[$name] = true;
            $this->blocks[$name] = sprintf('@phpstan-type %s %s', $name, Enums::union($set['values']));
        }
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

        // A field the document types as null and nothing else. It is not an absence of information:
        // `links.first` on a cursor paginator is null on every page there will ever be, and saying
        // `mixed` about it asks the caller to handle a value that cannot arrive.
        if ($rest === [] && $nullable) {
            return 'null';
        }

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

        $written = Enums::reading($schema);

        if ($written !== null) {
            return $written;
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
 * What the document says a body must be, in the little the validator understands.
 *
 * A pruned copy rather than the schema itself: the keywords kept are the ones Validator checks, so
 * what is written here and what is enforced cannot drift apart. References are resolved on the way
 * in, since a caller reading a refusal wants the field, not a pointer.
 *
 * Only bodies. A query parameter is an argument with a type on it, and a wrong one does not compile.
 */
final class Rules
{
    /** The bounds worth carrying: each is a number, and each reads the same on either side. */
    public const BOUNDS = ['minLength', 'maxLength', 'minimum', 'maximum', 'minItems', 'maxItems'];

    /** @var array<string, array<string, mixed>> */
    private array $bodies = [];

    /**
     * @param list<Endpoint>       $endpoints
     * @param array<string, mixed> $document
     */
    public function __construct(array $endpoints, private readonly array $document)
    {
        foreach ($endpoints as $endpoint) {
            $schema = $endpoint->spec['requestBody']['content']['application/json']['schema'] ?? null;

            if (is_array($schema)) {
                $this->bodies[$endpoint->method . ' ' . $endpoint->path] = $this->node($schema, []);
            }
        }

        ksort($this->bodies);
    }

    /** @return array<string, array<string, mixed>> */
    public function bodies(): array
    {
        return $this->bodies;
    }

    /**
     * @param array<string, mixed> $schema
     * @param list<string>         $seen
     *
     * @return array<string, mixed>
     */
    private function node(array $schema, array $seen): array
    {
        if (isset($schema['$ref']) && is_string($schema['$ref'])) {
            $name = substr($schema['$ref'], (int) strrpos($schema['$ref'], '/') + 1);

            // A component reaching itself has no bottom to check against, so it is left unchecked
            // rather than followed. Nothing in the document does this today.
            if (in_array($name, $seen, true)) {
                return [];
            }

            $schemas = $this->document['components']['schemas'] ?? [];
            $held = is_array($schemas) && is_array($schemas[$name] ?? null) ? $schemas[$name] : [];

            return $this->node($held, array_merge($seen, [$name]));
        }

        if (isset($schema['oneOf']) && is_array($schema['oneOf'])) {
            return $this->choice($schema, $seen);
        }

        $declared = $schema['type'] ?? null;
        $types = is_array($declared) ? $declared : ($declared === null ? [] : [$declared]);
        $rest = array_values(array_filter($types, static fn ($one): bool => $one !== 'null'));
        $node = [];

        // Two types at once is the document declining to say, and so is none. Either way the value
        // is carried as it came and only what surrounds it is checked.
        if (count($rest) === 1) {
            $node['type'] = (string) $rest[0];
        }

        if (in_array('null', $types, true)) {
            $node['null'] = true;
        }

        if (isset($schema['enum']) && is_array($schema['enum']) && $schema['enum'] !== []) {
            $node['enum'] = array_values($schema['enum']);
        }

        if (in_array($schema['format'] ?? null, ['date', 'date-time'], true)) {
            $node['format'] = (string) $schema['format'];
        }

        // Published only where PCRE and ECMA read it the same way — see the API's schema builder —
        // so what arrives here is safe to hand to preg_match as it stands.
        if (isset($schema['pattern']) && is_string($schema['pattern'])) {
            $node['pattern'] = $schema['pattern'];
        }

        foreach (self::BOUNDS as $bound) {
            if (isset($schema[$bound]) && is_int($schema[$bound])) {
                $node[$bound] = $schema[$bound];
            }
        }

        if (($node['type'] ?? '') === 'object' && is_array($schema['properties'] ?? null)) {
            $required = is_array($schema['required'] ?? null) ? $schema['required'] : [];
            $node['required'] = array_values(array_map(strval(...), $required));
            $node['properties'] = [];

            foreach ($schema['properties'] as $key => $property) {
                $node['properties'][(string) $key] = is_array($property) ? $this->node($property, $seen) : [];
            }
        }

        if (($node['type'] ?? '') === 'array' && is_array($schema['items'] ?? null)) {
            $node['items'] = $this->node($schema['items'], $seen);
        }

        return $node;
    }

    /**
     * A value the document allows more than one shape for.
     *
     * Where a discriminator names the field that decides, the branches are kept under the values it
     * takes: the check is then against the one shape meant rather than against all of them, and a
     * refusal names the field that is actually wrong. Without one, every branch is tried and the
     * value has to satisfy some branch.
     *
     * @param array<string, mixed> $schema
     * @param list<string>         $seen
     *
     * @return array<string, mixed>
     */
    private function choice(array $schema, array $seen): array
    {
        $mapping = $schema['discriminator']['mapping'] ?? null;
        $on = $schema['discriminator']['propertyName'] ?? null;

        if (is_string($on) && is_array($mapping) && $mapping !== []) {
            $branches = [];

            foreach ($mapping as $value => $ref) {
                $branches[(string) $value] = $this->node(['$ref' => $ref], $seen);
            }

            return ['oneOf' => $branches, 'on' => $on];
        }

        $branches = [];

        foreach ($schema['oneOf'] as $one) {
            if (is_array($one)) {
                $branches[] = $this->node($one, $seen);
            }
        }

        return ['oneOf' => $branches];
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

    /**
     * What the document says the fields of this body are, as paths a caller can find.
     *
     * A shape carries no room for prose — `array{external_id?: string}` has nowhere to say what an
     * external id is — and the body is one argument, so its own docblock is where a field's
     * description can be read. Nested and listed fields are written as the path to them:
     * `users[].external_id`, `tasks[].items[].title`.
     *
     * @return list<array{path: string, prose: string}>
     */
    public function fields(): array
    {
        $schema = $this->spec['requestBody']['content']['application/json']['schema'] ?? null;

        return is_array($schema) ? $this->described($schema, '') : [];
    }

    /**
     * @param array<string, mixed> $schema
     *
     * @return list<array{path: string, prose: string}>
     */
    private function described(array $schema, string $at): array
    {
        $found = [];

        // A body the document gives more than one shape for: each branch is walked, and a field two
        // branches share is named once.
        foreach ($schema['oneOf'] ?? [] as $one) {
            if (is_array($one)) {
                $found = array_merge($found, $this->described($one, $at));
            }
        }

        if (isset($schema['items']) && is_array($schema['items'])) {
            $found = array_merge($found, $this->described($schema['items'], $at . '[]'));
        }

        foreach ($schema['properties'] ?? [] as $key => $property) {
            if (!is_array($property)) {
                continue;
            }

            $path = $at === '' ? (string) $key : $at . '.' . $key;
            $prose = $property['description'] ?? null;

            if (is_string($prose) && $prose !== '') {
                $found[] = ['path' => $path, 'prose' => $prose];
            }

            $found = array_merge($found, $this->described($property, $path));
        }

        $seen = [];
        $once = [];

        foreach ($found as $held) {
            if (!isset($seen[$held['path']])) {
                $seen[$held['path']] = true;
                $once[] = $held;
            }
        }

        return $once;
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

// Collected before any shape is built: naming a set needs the resource that carries it, which is
// the endpoint rather than the schema, and a shape referring to one needs the name to exist first.
$enums = new Enums($endpoints, $document);
$rules = new Rules($endpoints, $document);
$shapes = new Shapes($document, $enums);

foreach ($endpoints as $endpoint) {
    $endpoint->types($shapes);
}

emit($endpoints, $shapes, $enums, $rules);

printf(
    "%d operations, %d shapes, %d sets, %d bodies with rules.\n",
    count($endpoints),
    count($shapes->blocks()) - count($enums->classes()),
    count($enums->classes()),
    count($rules->bodies()),
);
