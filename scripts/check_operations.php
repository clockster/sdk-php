<?php

declare(strict_types=1);

/**
 * Every operation in the specification must be reachable on the client.
 *
 * A naming scheme that shortens method names can collapse two operations onto one silently, and a
 * namespace nothing reaches would pass a count that only added methods up. So this walks the
 * client from the root, the way a caller does.
 */

require __DIR__ . '/../vendor/autoload.php';

const SPEC = 'openapi/company-v3.json';

const METHODS = ['get', 'post', 'put', 'patch', 'delete'];

/**
 * @param list<string> $reached
 */
function walk(object $held, string $prefix, array &$reached): void
{
    $reflection = new ReflectionObject($held);

    foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
        $name = $method->getName();

        // listAll() walks the pages of list(); it is one operation between them rather than two.
        if ($name === '__construct' || (str_ends_with($name, 'All') && $reflection->hasMethod(substr($name, 0, -3)))) {
            continue;
        }

        $reached[] = sprintf('%s->%s()', $prefix, $name);
    }

    foreach ($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
        $value = $property->getValue($held);

        if (is_object($value)) {
            walk($value, sprintf('%s->%s', $prefix, $property->getName()), $reached);
        }
    }
}

$document = json_decode((string) file_get_contents(SPEC), true, flags: JSON_THROW_ON_ERROR);
$operations = 0;

foreach ($document['paths'] as $item) {
    $operations += count(array_intersect(array_keys($item), METHODS));
}

$reached = [];

walk(new Clockster\Client('token'), 'clockster', $reached);

if (count($reached) !== $operations) {
    fwrite(STDERR, sprintf(
        "%d operations in the specification, %d reachable on the client. Two operations whose names "
        . "collide are silently merged; check OVERRIDES in scripts/generate.php.\n",
        $operations,
        count($reached),
    ));

    exit(1);
}

printf("%d operations, all reachable.\n", count($reached));
