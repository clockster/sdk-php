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

/**
 * @param list<Endpoint> $endpoints
 */
function emit(array $endpoints, Shapes $shapes): void
{
    if (!is_dir(OUT)) {
        mkdir(OUT, 0o755, true);
    }

    foreach (glob(OUT . '/*.php') ?: [] as $stale) {
        unlink($stale);
    }

    writeShapes($shapes);

    $tree = namespaces($endpoints);

    foreach ($tree as $class => $node) {
        writeNamespace($class, $node, $endpoints, $shapes);
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
 * @param array{path: string, children: array<string, string>} $node
 * @param list<Endpoint>                                       $endpoints
 */
function writeNamespace(string $class, array $node, array $endpoints, Shapes $shapes): void
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
        $body[] = method($endpoint, $shapes);

        if ($endpoint->rowType !== null) {
            $body[] = '';
            $body[] = walk($endpoint);
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

function method(Endpoint $endpoint, Shapes $shapes): string
{
    $arguments = arguments($endpoint);
    $lines = docblock($endpoint, $arguments, $endpoint->returns, '@throws ApiException|TransportException');

    $lines[] = opening(sprintf('    public function %s(', $endpoint->name), $arguments, 'array');
    $lines[] = call($endpoint);
    $lines[] = '    }';

    return implode("\n", $lines);
}

function walk(Endpoint $endpoint): string
{
    $arguments = array_values(array_filter(
        arguments($endpoint),
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
function arguments(Endpoint $endpoint): array
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
        $held = queryArgument($parameter);

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
function queryArgument(array $parameter): array
{
    $schema = is_array($parameter['schema'] ?? null) ? $parameter['schema'] : [];
    $declared = $schema['type'] ?? 'string';
    $types = is_array($declared) ? $declared : [$declared];
    $rest = array_values(array_filter($types, static fn ($one): bool => $one !== 'null'));
    $held = (string) ($rest[0] ?? 'string');
    $required = ($parameter['required'] ?? false) === true;

    if ($held === 'array') {
        $items = is_array($schema['items'] ?? null) ? $schema['items'] : [];
        $item = SCALARS[$items['type'] ?? 'string'] ?? 'string';

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

    return [
        'name' => camel((string) $parameter['name']),
        'type' => $required ? $type : '?' . $type,
        'default' => $required ? null : 'null',
        'doc' => '',
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
