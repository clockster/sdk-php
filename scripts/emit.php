<?php

declare(strict_types=1);

/**
 * Writing the files. The half of the generator that decides what the source looks like; generate.php
 * decides what is in it.
 */

const HEAD = <<<'PHP'
<?php

declare(strict_types=1);

namespace Clockster\Generated;


PHP;

const ENUM_HEAD = <<<'PHP'
<?php

declare(strict_types=1);

namespace Clockster\Generated\Enum;


PHP;

/**
 * @param list<Endpoint> $endpoints
 */
function emit(array $endpoints, Shapes $shapes, Enums $enums, Rules $rules): void
{
    foreach ([OUT, OUT . '/Enum'] as $directory) {
        if (!is_dir($directory)) {
            mkdir($directory, 0o755, true);
        }

        // Swept rather than overwritten, so a set the document stops naming leaves with it.
        foreach (glob($directory . '/*.php') ?: [] as $stale) {
            unlink($stale);
        }
    }

    writeShapes($shapes);
    writeEnums($enums);
    writeConstraints($rules);

    $tree = namespaces($endpoints);

    foreach ($tree as $class => $node) {
        writeNamespace($class, $node, $endpoints, $shapes, $enums);
    }
}

/**
 * The groups of operations, keyed by class name. The root is the abstract class the client extends.
 *
 * @param list<Endpoint> $endpoints
 *
 * @return array<string, array{path: string, children: array<string, string>}>
 */
function namespaces(array $endpoints): array
{
    $tree = ['Api' => ['path' => '', 'children' => []]];

    foreach ($endpoints as $endpoint) {
        $parent = 'Api';
        $path = '';

        foreach ($endpoint->group() as $depth => $property) {
            $path = $path === '' ? $property : $path . '->' . $property;
            $class = pascal(implode('_', array_slice($endpoint->segments, 0, $depth + 1)));

            $tree[$parent]['children'][$property] = $class;
            $tree[$class] ??= ['path' => $path, 'children' => []];

            $parent = $class;
        }
    }

    foreach ($tree as &$node) {
        ksort($node['children']);
    }

    return $tree;
}

function writeShapes(Shapes $shapes): void
{
    $lines = [
        '/**',
        ' * Every shape the Company API answers with or accepts, as array shapes a static analyser',
        ' * reads.',
        ' *',
        ' * Generated from ' . SPEC . ' — see scripts/generate.php. A key marked optional is absent',
        ' * rather than null: an `include` relation is not there unless it was asked for, and a field',
        ' * left out of a write keeps whatever is stored where a null one clears it.',
        ' *',
        ' * They are documentation and nothing else. What a method answers is a plain array, and a key',
        ' * the API adds tomorrow is in it whether or not this file knows the name.',
        ' *',
        ' * The sets of values come first, written out. Every one of them is on something you send,',
        ' * never in an answer, so writing a field as one closes nothing you read — and the constants',
        ' * for each are a class of their own under Enum.',
        ' *',
    ];

    foreach ($shapes->blocks() as $block) {
        $lines[] = ' * ' . $block;
    }

    $lines[] = ' */';
    $lines[] = 'final class Shapes';
    $lines[] = '{';
    $lines[] = '}';

    write('Shapes', implode("\n", $lines) . "\n");
}

/**
 * What the document says a body must be, as data for Clockster\Validator to walk.
 *
 * Nothing reads this unless a caller asks for it, and asking is one argument to the client. The
 * shapes in Shapes.php say the same thing to a static analyser, which is the better place to hear
 * it — this is for the run where nobody ran one.
 */
function writeConstraints(Rules $rules): void
{
    $lines = ['/**'];
    $lines = array_merge($lines, prose(
        'What the Company API says each body must be, in the little of the document Validator '
        . 'checks: which fields a body names, which of them it insists on, what type each takes, '
        . 'the sets and the bounds and the two date formats.',
        '',
    ));
    $lines[] = ' *';
    $lines = array_merge($lines, prose(
        'Generated from ' . SPEC . ' — see scripts/generate.php. Read only where a client was built '
        . 'with `validate: true`, and not otherwise: a body reaches the API as it was handed over, '
        . 'and this is a courtesy on the way rather than a gate. Keyed by the method and the path '
        . 'as the document writes them, so a path holding an id is matched rather than looked up.',
        '',
    ));
    $lines[] = ' */';
    $lines[] = 'final class Constraints';
    $lines[] = '{';
    $lines[] = '    /** @var array<string, array<string, mixed>> */';
    $lines[] = '    public const BODIES = ' . exported($rules->bodies(), 1) . ';';
    $lines[] = '}';

    write('Constraints', implode("\n", $lines) . "\n");
}

/**
 * A value as the PHP that reads it back. Broken over lines where it does not fit on one, which is
 * how the rest of this repository is written; pint settles whatever is left.
 */
function exported(mixed $value, int $depth = 0): string
{
    if (!is_array($value)) {
        return scalarly($value);
    }

    if ($value === []) {
        return '[]';
    }

    $list = array_is_list($value);
    $written = [];

    foreach ($value as $key => $held) {
        $written[] = ($list ? '' : scalarly($key) . ' => ') . exported($held, $depth + 1);
    }

    // A short list of values reads better as one line than as one line each.
    $one = '[' . implode(', ', $written) . ']';

    if (strpos($one, "\n") === false && strlen($one) + 4 * ($depth + 1) <= WIDTH) {
        return $one;
    }

    $pad = str_repeat('    ', $depth + 1);

    return "[\n" . $pad . implode(",\n" . $pad, $written) . ",\n" . str_repeat('    ', $depth) . ']';
}

function scalarly(mixed $value): string
{
    if (is_string($value)) {
        return "'" . str_replace(['\\', "'"], ['\\\\', "\\'"], $value) . "'";
    }

    if (is_bool($value)) {
        return $value ? 'true' : 'false';
    }

    if (is_int($value) || is_float($value)) {
        return (string) $value;
    }

    return 'null';
}

/** One class of constants per set of values, under Enum so they never crowd the operations. */
function writeEnums(Enums $enums): void
{
    foreach ($enums->classes() as $name => $set) {
        writeEnum($name, $set);
    }
}

/**
 * The constants are untyped on purpose. A `const string` declares the type and loses the value with
 * it, where a bare one is read as the literal it holds — which is the whole point, since the field
 * it goes into is written as the set rather than as a string.
 *
 * @param array{word: string, values: list<string>, sites: list<string>} $set
 */
function writeEnum(string $name, array $set): void
{
    $constants = [];
    $claimed = [];

    foreach ($set['values'] as $value) {
        $constant = constantName($value);

        if (isset($claimed[$constant])) {
            fail(sprintf('%s reads %s for both %s and %s.', $name, $constant, $claimed[$constant], $value));
        }

        $claimed[$constant] = $value;
        $constants[] = $constant;
    }

    $lines = ['/**'];
    $lines = array_merge($lines, prose(sprintf('What `%s` is allowed to be.', $set['word']), ''));
    $lines[] = ' *';
    $lines = array_merge($lines, prose('Sent in ' . implode(', ', $set['sites']) . '.', ''));
    $lines[] = ' *';
    $lines = array_merge($lines, prose(sprintf(
        'Constants rather than the cases of an enum, so one goes wherever the string goes: '
        . '`%s::%s` is `\'%s\'`, and a static analyser reads the two as one value. Closed on the '
        . 'way in and only there — an answer naming something this class does not is still a '
        . 'string, and still reaches you.',
        $name,
        $constants[0],
        $set['values'][0],
    ), ''));
    $lines[] = ' */';
    $lines[] = 'final class ' . $name;
    $lines[] = '{';

    foreach ($set['values'] as $index => $value) {
        $lines[] = sprintf("    public const %s = '%s';", $constants[$index], $value);
    }

    $lines[] = '';
    $lines[] = sprintf('    /** @return list<%s> */', Enums::union($set['values']));
    $lines[] = '    public static function values(): array';
    $lines[] = '    {';
    $lines[] = '        return [';

    foreach ($constants as $constant) {
        $lines[] = sprintf('            self::%s,', $constant);
    }

    $lines[] = '        ];';
    $lines[] = '    }';
    $lines[] = '}';

    file_put_contents(OUT . '/Enum/' . $name . '.php', ENUM_HEAD . implode("\n", $lines) . "\n");
}

/** `user.created` is `USER_CREATED`, and a value opening on a digit is not a name at all. */
function constantName(string $value): string
{
    $written = strtoupper(trim((string) preg_replace('/[^a-zA-Z0-9]+/', '_', $value), '_'));

    return $written === '' || ctype_digit($written[0]) ? 'VALUE_' . $written : $written;
}

/**
 * @param array{path: string, children: array<string, string>} $node
 * @param list<Endpoint>                                       $endpoints
 */
function writeNamespace(string $class, array $node, array $endpoints, Shapes $shapes, Enums $enums): void
{
    $root = $class === 'Api';
    $mine = array_values(array_filter(
        $endpoints,
        static fn (Endpoint $endpoint): bool => ($endpoint->namespace === '' ? 'Api' : $endpoint->namespace) === $class,
    ));

    $body = [];

    foreach ($node['children'] as $property => $child) {
        $body[] = sprintf('    /** The operations of `$clockster->%s`. */', trim($node['path'] . '->' . $property, '->'));
        $body[] = sprintf('    public readonly %s $%s;', $child, $property);
        $body[] = '';
    }

    $body[] = sprintf('    public function __construct(%s readonly Caller $caller)', $root ? 'protected' : 'private');
    $body[] = '    {';

    foreach ($node['children'] as $property => $child) {
        $body[] = sprintf('        $this->%s = new %s($caller);', $property, $child);
    }

    $body[] = '    }';

    foreach ($mine as $endpoint) {
        $body[] = '';
        $body[] = method($endpoint, $enums);

        if ($endpoint->rowType !== null) {
            $body[] = '';
            $body[] = walk($endpoint, $enums);
        }
    }

    $written = implode("\n", $body);

    $lines = ['/**'];

    if ($root) {
        $lines[] = ' * The operations of the Company API, as they are called.';
        $lines[] = ' *';
        $lines[] = ' * Generated from ' . SPEC . ' — see scripts/generate.php. Clockster\Client extends this,';
        $lines[] = ' * so everything here hangs off the client a caller builds.';
    } else {
        $lines[] = sprintf(' * The operations of `$clockster->%s`.', $node['path']);
    }

    $imports = imports($written, $shapes);

    if ($imports !== []) {
        $lines[] = ' *';

        foreach ($imports as $import) {
            $lines[] = ' * ' . $import;
        }
    }

    $lines[] = ' */';
    $lines[] = sprintf('%sclass %s', $root ? 'abstract ' : 'final ', $class);
    $lines[] = '{';
    $lines[] = $written;
    $lines[] = '}';

    write($class, implode("\n", $lines) . "\n");
}

/**
 * The shapes a file names, imported so a static analyser resolves them.
 *
 * @return list<string>
 */
function imports(string $body, Shapes $shapes): array
{
    $found = [];

    foreach ($shapes->names() as $name) {
        if (preg_match('/\b' . preg_quote($name, '/') . '\b/', $body) === 1) {
            $found[] = sprintf('@phpstan-import-type %s from Shapes', $name);
        }
    }

    sort($found);

    return $found;
}

function method(Endpoint $endpoint, Enums $enums): string
{
    $arguments = arguments($endpoint, $enums);
    // A body is the only thing Validator reads, so it is the only thing that can be refused here
    // rather than by the API — and then only where the client was built to read one.
    $throws = $endpoint->bodyType === null
        ? '@throws ApiException|TransportException'
        : '@throws ApiException|InvalidBodyException|TransportException';
    $lines = docblock($endpoint, $arguments, $endpoint->returns, $throws);

    $lines[] = opening(sprintf('    public function %s(', $endpoint->name), $arguments, 'array');
    $lines[] = call($endpoint);
    $lines[] = '    }';

    return implode("\n", $lines);
}

function walk(Endpoint $endpoint, Enums $enums): string
{
    $arguments = array_values(array_filter(
        arguments($endpoint, $enums),
        static fn (array $argument): bool => ($argument['wire'] ?? null) !== 'cursor',
    ));

    $lines = docblock(
        $endpoint,
        $arguments,
        '\Generator<int, ' . $endpoint->rowType . '>',
        '@throws ApiException|TransportException',
        sprintf('Every row of %s(), a page at a time.', $endpoint->name),
        [
            'A refused page is thrown where it was refused, so half a listing is never mistaken for the',
            'whole of one. A cursor belongs to the filters it was issued under: change them and walk',
            'again.',
        ],
    );

    $passed = [];

    foreach ($arguments as $argument) {
        $passed[] = sprintf('%s: $%s', $argument['name'], $argument['name']);
    }

    $passed[] = 'cursor: $cursor';

    $lines[] = opening(sprintf('    public function %sAll(', $endpoint->name), $arguments, 'Generator');
    $lines[] = '        $cursor = null;';
    $lines[] = '        $seen = [];';
    $lines[] = '';
    $one = sprintf('            $page = $this->%s(%s);', $endpoint->name, implode(', ', $passed));

    $lines[] = '        while (true) {';
    $lines[] = strlen($one) <= WIDTH
        ? $one
        : sprintf(
            "            \$page = \$this->%s(\n                %s,\n            );",
            $endpoint->name,
            implode(",\n                ", $passed),
        );
    $lines[] = '';
    $lines[] = '            foreach ($page[\'data\'] as $row) {';
    $lines[] = '                yield $row;';
    $lines[] = '            }';
    $lines[] = '';
    $lines[] = '            $cursor = $page[\'meta\'][\'next_cursor\'] ?? null;';
    $lines[] = '';
    $lines[] = '            // A cursor that repeats would page until the process is killed, which is worse';
    $lines[] = '            // than stopping.';
    $lines[] = '            if ($cursor === null || isset($seen[$cursor])) {';
    $lines[] = '                return;';
    $lines[] = '            }';
    $lines[] = '';
    $lines[] = '            $seen[$cursor] = true;';
    $lines[] = '        }';
    $lines[] = '    }';

    return implode("\n", $lines);
}

/**
 * What a method takes: path parameters, then the body or the file, then the query, then the key
 * that makes a retry safe. PHP wants the ones without a default first, and named arguments mean
 * the order costs a caller nothing.
 *
 * @return list<array{name: string, type: string, default: string|null, doc: string, prose: string, wire?: string}>
 */
function arguments(Endpoint $endpoint, Enums $enums): array
{
    $arguments = [];

    foreach ($endpoint->pathParams as $parameter) {
        $type = ($parameter['schema']['type'] ?? 'integer') === 'string' ? 'string' : 'int';
        $arguments[] = [
            'name' => camel((string) $parameter['name']),
            'type' => $type,
            'default' => null,
            'doc' => '',
            'prose' => (string) ($parameter['description'] ?? ''),
        ];
    }

    if ($endpoint->bodyType !== null) {
        $arguments[] = [
            'name' => 'body',
            'type' => 'array',
            'default' => null,
            'doc' => $endpoint->bodyType,
            'prose' => '',
        ];
    }

    if ($endpoint->upload) {
        $arguments[] = [
            'name' => 'file',
            'type' => 'string',
            'default' => null,
            'doc' => '',
            'prose' => 'The bytes to store, as read from the file.',
        ];
        $arguments[] = [
            'name' => 'filename',
            'type' => 'string',
            'default' => "'upload'",
            'doc' => '',
            'prose' => 'The name the bytes travel under.',
        ];

        foreach ($endpoint->uploadFields as $field) {
            $arguments[] = [
                'name' => camel($field),
                'type' => '?string',
                'default' => 'null',
                'doc' => '',
                'prose' => '',
                'wire' => $field,
            ];
        }
    }

    $required = [];
    $optional = [];

    foreach ($endpoint->query as $parameter) {
        $held = queryArgument($parameter, $endpoint->resource(), $enums);

        if ($held['default'] === null) {
            $required[] = $held;
        } else {
            $optional[] = $held;
        }
    }

    $arguments = array_merge($arguments, $required, $optional);

    if ($endpoint->idempotent) {
        $arguments[] = [
            'name' => 'idempotencyKey',
            'type' => '?string',
            'default' => 'null',
            'doc' => '',
            'prose' => 'A value of your own, so a retry of this write is answered with the first result '
                . 'rather than performed again.',
        ];
    }

    return $arguments;
}

/**
 * @param array<string, mixed> $parameter
 *
 * @return array{name: string, type: string, default: string|null, doc: string, prose: string, wire: string}
 */
function queryArgument(array $parameter, string $namespace, Enums $enums): array
{
    $schema = is_array($parameter['schema'] ?? null) ? $parameter['schema'] : [];
    $declared = $schema['type'] ?? 'string';
    $types = is_array($declared) ? $declared : [$declared];
    $rest = array_values(array_filter($types, static fn ($one): bool => $one !== 'null'));
    $held = (string) ($rest[0] ?? 'string');
    $required = ($parameter['required'] ?? false) === true;

    if ($held === 'array') {
        $items = is_array($schema['items'] ?? null) ? $schema['items'] : [];
        $values = Enums::scalars($items);
        $item = $values === null
            ? (SCALARS[$items['type'] ?? 'string'] ?? 'string')
            : $enums->written($namespace, $values);

        return [
            'name' => camel((string) $parameter['name']),
            'type' => 'array',
            'default' => '[]',
            'doc' => 'list<' . $item . '>',
            'prose' => (string) ($parameter['description'] ?? ''),
            'wire' => (string) $parameter['name'],
        ];
    }

    $type = SCALARS[$held] ?? 'string';
    $values = Enums::scalars($schema);

    return [
        'name' => camel((string) $parameter['name']),
        'type' => $required ? $type : '?' . $type,
        'default' => $required ? null : 'null',
        'doc' => $values === null ? '' : $enums->written($namespace, $values),
        'prose' => (string) ($parameter['description'] ?? ''),
        'wire' => (string) $parameter['name'],
    ];
}

/**
 * The line a method opens with, on one line where it fits and broken the way PSR-12 breaks it
 * where it does not.
 *
 * @param list<array{name: string, type: string, default: string|null, doc: string, prose: string, wire?: string}> $arguments
 */
function opening(string $head, array $arguments, string $answers): string
{
    $written = [];

    foreach ($arguments as $argument) {
        $written[] = sprintf(
            '%s $%s%s',
            $argument['type'],
            $argument['name'],
            $argument['default'] === null ? '' : ' = ' . $argument['default'],
        );
    }

    $one = $head . implode(', ', $written) . '): ' . $answers;

    if (strlen($one) <= WIDTH) {
        return $one . "\n    {";
    }

    return $head . "\n        " . implode(",\n        ", $written) . ",\n    ): " . $answers . ' {';
}

/**
 * @param list<array{name: string, type: string, default: string|null, doc: string, prose: string, wire?: string}> $arguments
 * @param list<string>                                                                                            $extra
 *
 * @return list<string>
 */
function docblock(
    Endpoint $endpoint,
    array $arguments,
    string $returns,
    string $throws,
    ?string $summary = null,
    array $extra = [],
): array {
    $lines = ['    /**'];
    $lines[] = '     * ' . ($summary ?? stopped((string) ($endpoint->spec['summary'] ?? $endpoint->name)));

    $description = $extra === [] ? (string) ($endpoint->spec['description'] ?? '') : implode("\n", $extra);

    if ($description !== '') {
        $lines[] = '     *';
        $lines = array_merge($lines, prose($description, '    '));
    }

    $lines = array_merge($lines, fieldNotes($endpoint->fields()));

    $documented = [];

    foreach ($arguments as $argument) {
        $type = $argument['doc'] !== '' ? $argument['doc'] : trim($argument['type'], '?');
        $type = str_starts_with($argument['type'], '?') ? $type . '|null' : $type;

        if ($argument['doc'] === '' && $argument['prose'] === '') {
            continue;
        }

        $documented = array_merge($documented, prose(
            sprintf('@param %s $%s %s', $type, $argument['name'], $argument['prose']),
            '    ',
        ));
    }

    if ($documented !== []) {
        $lines[] = '     *';
        $lines = array_merge($lines, $documented);
    }

    $lines[] = '     *';
    $lines[] = '     * @return ' . $returns;
    $lines[] = '     *';
    $lines[] = '     * ' . $throws;
    $lines[] = '     */';

    return $lines;
}

/**
 * The fields of a body, listed under their own heading.
 *
 * An array shape has nowhere to put prose — `array{external_id?: string}` cannot say what an
 * external id is — and the body is one argument, so the method's own docblock is where a field's
 * description ends up being read. Written as the path to the field, so a nested one is findable.
 *
 * @param list<array{path: string, prose: string}> $fields
 *
 * @return list<string>
 */
function fieldNotes(array $fields): array
{
    if ($fields === []) {
        return [];
    }

    $lines = ['     *', '     * What each field is:', '     *'];

    foreach ($fields as $field) {
        $written = sprintf('`%s` — %s', $field['path'], $field['prose']);

        foreach (explode("\n", wordwrap($written, WIDTH - 9)) as $index => $line) {
            $lines[] = '     * ' . ($index === 0 ? '- ' : '  ') . $line;
        }
    }

    return $lines;
}

/** gofmt-style: a lone unpunctuated line reads as a heading, and this is a sentence. */
function stopped(string $summary): string
{
    return preg_match('/[.!?]$/', $summary) === 1 ? $summary : $summary . '.';
}

function call(Endpoint $endpoint): string
{
    $parts = [sprintf("'%s'", $endpoint->method), path($endpoint)];
    $query = [];

    foreach ($endpoint->query as $parameter) {
        $query[] = sprintf("                '%s' => $%s,", $parameter['name'], camel((string) $parameter['name']));
    }

    if ($query !== []) {
        $parts[] = "[\n" . implode("\n", $query) . "\n            ]";
    }

    if ($endpoint->bodyType !== null) {
        $parts[] = 'body: $body';
    }

    if ($endpoint->upload) {
        $fields = [];

        foreach ($endpoint->uploadFields as $field) {
            $fields[] = sprintf("'%s' => $%s", $field, camel($field));
        }

        $parts[] = sprintf(
            'upload: new Upload($file, $filename%s)',
            $fields === [] ? '' : ', [' . implode(', ', $fields) . ']',
        );
    }

    if ($endpoint->idempotent) {
        $parts[] = 'idempotencyKey: $idempotencyKey';
    }

    $call = sprintf("\$this->caller->call(\n            %s,\n        );", implode(",\n            ", $parts));

    if ($endpoint->returns === 'array<string, mixed>') {
        return '        return ' . $call;
    }

    // Annotated rather than checked: nothing here validates, and the shape is what the document
    // says the API answers with rather than something this package confirmed.
    return sprintf(
        "        /** @var %s \$answer */\n        \$answer = %s\n\n        return \$answer;",
        $endpoint->returns,
        $call,
    );
}

function path(Endpoint $endpoint): string
{
    if ($endpoint->pathParams === []) {
        return sprintf("'%s'", $endpoint->path);
    }

    $written = $endpoint->path;
    $arguments = [];

    foreach ($endpoint->pathParams as $parameter) {
        $name = (string) $parameter['name'];
        $verb = ($parameter['schema']['type'] ?? 'integer') === 'string' ? '%s' : '%d';
        $written = str_replace('{' . $name . '}', $verb, $written);
        $arguments[] = '$' . camel($name);
    }

    return sprintf("sprintf('%s', %s)", $written, implode(', ', $arguments));
}

function write(string $class, string $body): void
{
    // What the file names rather than a fixed list: Shapes.php holds no code and imports nothing.
    $candidates = [
        'ApiException' => 'Clockster\Exception\ApiException',
        'InvalidBodyException' => 'Clockster\Exception\InvalidBodyException',
        'TransportException' => 'Clockster\Exception\TransportException',
        'Caller' => 'Clockster\Http\Caller',
        'new Upload(' => 'Clockster\Http\Upload',
        'Generator' => 'Generator',
    ];

    $uses = [];

    foreach ($candidates as $token => $use) {
        if (str_contains($body, $token)) {
            $uses[] = $use;
        }
    }

    if ($uses === []) {
        file_put_contents(OUT . '/' . $class . '.php', HEAD . $body);

        return;
    }

    sort($uses);

    $head = HEAD . implode("\n", array_map(static fn (string $use): string => 'use ' . $use . ';', $uses)) . "\n\n";

    file_put_contents(OUT . '/' . $class . '.php', $head . $body);
}
