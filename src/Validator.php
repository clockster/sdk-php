<?php

declare(strict_types=1);

namespace Clockster;

use Clockster\Exception\InvalidBodyException;
use Clockster\Generated\Constraints;
use DateTimeImmutable;

/**
 * Reading a body against the document before it is sent.
 *
 * Off unless a caller asks, because the rule everywhere else here is that a call goes as it was
 * written: the document describes the API rather than being it, and a body this refuses may be one
 * the API would have taken. Turning it on trades that for the other direction — a misspelled field,
 * a typo in a set, a required value nobody filled in, found where it was written rather than as a
 * 422 against a batch of a hundred people.
 *
 *     $clockster = new Client($token, validate: true);
 *
 * Worth having on where you develop and in CI. Worth leaving off in production, where a refusal
 * this package invented is one nobody can act on. The shapes in Clockster\Generated\Shapes say the
 * same thing to a static analyser, earlier and for free; this is for the run where nobody ran one.
 *
 * It can also be called on its own, against a body nothing is about to send:
 *
 *     Validator::check('POST', '/company/v3/users/upsert', ['users' => $people]);
 */
final class Validator
{
    /**
     * @param array<string, mixed> $body
     *
     * @throws InvalidBodyException where the document says this body is wrong
     */
    public static function check(string $method, string $path, array $body): void
    {
        $route = self::route($method, $path);

        if ($route === null) {
            return;
        }

        $problems = [];

        self::walk($body, Constraints::BODIES[$route], 'body', $problems);

        if ($problems !== []) {
            throw new InvalidBodyException($route, $problems);
        }
    }

    /**
     * The document's name for this call, where it describes a body for one.
     *
     * A path is asked about as it goes out, with the id already in it, where the document writes
     * the id as a placeholder — so the handful of routes holding one are matched rather than looked
     * up. Everything else is a straight hit.
     */
    private static function route(string $method, string $path): ?string
    {
        $asked = $method . ' ' . $path;

        if (isset(Constraints::BODIES[$asked])) {
            return $asked;
        }

        foreach (array_keys(Constraints::BODIES) as $route) {
            if (!str_contains($route, '{')) {
                continue;
            }

            $parts = preg_split('/\{[^}]+\}/', $route) ?: [];
            $pattern = '#^' . implode('[^/]+', array_map(
                static fn (string $part): string => preg_quote($part, '#'),
                $parts,
            )) . '$#';

            if (preg_match($pattern, $asked) === 1) {
                return $route;
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed>        $rules
     * @param array<string, list<string>> $problems
     */
    private static function walk(mixed $value, array $rules, string $at, array &$problems): void
    {
        if (isset($rules['oneOf']) && is_array($rules['oneOf'])) {
            self::choice($value, $rules, $at, $problems);

            return;
        }

        if ($value === null) {
            if (($rules['null'] ?? false) !== true) {
                $problems[$at][] = 'cannot be null. Leave the key out to keep what is stored.';
            }

            return;
        }

        $type = $rules['type'] ?? null;

        // A wrong type makes everything after it meaningless, so it is the only thing said.
        if (is_string($type) && !self::holds($value, $type)) {
            $problems[$at][] = sprintf('wants %s, and this is %s.', self::wanted($type), self::given($value));

            return;
        }

        if (isset($rules['enum']) && is_array($rules['enum']) && !in_array($value, $rules['enum'], true)) {
            $problems[$at][] = sprintf(
                'wants one of %s, and this is %s.',
                implode(', ', array_map(self::written(...), $rules['enum'])),
                self::written($value),
            );
        }

        if (is_string($value)) {
            self::text($value, $rules, $at, $problems);
        }

        if (is_int($value) || is_float($value)) {
            self::number($value, $rules, $at, $problems);
        }

        if (is_array($value) && $type === 'array') {
            self::items($value, $rules, $at, $problems);
        }

        if (is_array($value) && $type === 'object') {
            self::fields($value, $rules, $at, $problems);
        }
    }

    /**
     * A value the document allows more than one shape for.
     *
     * Where a field decides which, that field is read first: the value is then checked against the
     * one shape it says it is, and what comes back names the field that is wrong rather than saying
     * the whole thing matched nothing.
     *
     * @param array<string, mixed>        $rules
     * @param array<string, list<string>> $problems
     */
    private static function choice(mixed $value, array $rules, string $at, array &$problems): void
    {
        /** @var array<array-key, array<string, mixed>> $branches */
        $branches = $rules['oneOf'];
        $on = $rules['on'] ?? null;

        if (is_string($on)) {
            if (!is_array($value)) {
                $problems[$at][] = sprintf('wants an object, and this is %s.', self::given($value));

                return;
            }

            $chosen = $value[$on] ?? null;

            if (!is_string($chosen) || !isset($branches[$chosen])) {
                $problems[$at . '.' . $on][] = sprintf(
                    'says which shape this is, and wants one of %s.',
                    implode(', ', array_map(self::written(...), array_keys($branches))),
                );

                return;
            }

            self::walk($value, $branches[$chosen], $at, $problems);

            return;
        }

        foreach ($branches as $branch) {
            $tried = [];

            self::walk($value, $branch, $at, $tried);

            if ($tried === []) {
                return;
            }
        }

        $problems[$at][] = sprintf('matches none of the %d shapes the document allows here.', count($branches));
    }

    /**
     * @param array<string, mixed>        $rules
     * @param array<string, list<string>> $problems
     */
    private static function text(string $value, array $rules, string $at, array &$problems): void
    {
        $held = mb_strlen($value);
        $least = $rules['minLength'] ?? null;
        $most = $rules['maxLength'] ?? null;

        if (is_int($least) && $held < $least) {
            $problems[$at][] = $least === 1
                ? 'cannot be empty. Send null to clear it, or leave the key out to keep it.'
                : sprintf('wants %d characters or more, and this is %d.', $least, $held);
        }

        if (is_int($most) && $held > $most) {
            $problems[$at][] = sprintf('wants %d characters at most, and this is %d.', $most, $held);
        }

        $pattern = $rules['pattern'] ?? null;

        if (is_string($pattern) && !self::shaped($value, $pattern)) {
            $problems[$at][] = sprintf(
                'is not the shape the document gives it, %s. See what it is for beside it.',
                $pattern,
            );
        }

        $format = $rules['format'] ?? null;

        if ($format === 'date' && !self::dated($value)) {
            $problems[$at][] = sprintf('wants a date as YYYY-MM-DD, and this is %s.', self::written($value));
        }

        if ($format === 'date-time' && !self::instant($value)) {
            $problems[$at][] = sprintf('wants an instant as ISO 8601, and this is %s.', self::written($value));
        }
    }

    /**
     * @param array<string, mixed>        $rules
     * @param array<string, list<string>> $problems
     */
    private static function number(int|float $value, array $rules, string $at, array &$problems): void
    {
        $least = $rules['minimum'] ?? null;
        $most = $rules['maximum'] ?? null;

        if (is_int($least) && $value < $least) {
            $problems[$at][] = sprintf('wants %d or more, and this is %s.', $least, self::written($value));
        }

        if (is_int($most) && $value > $most) {
            $problems[$at][] = sprintf('wants %d or less, and this is %s.', $most, self::written($value));
        }
    }

    /**
     * @param array<array-key, mixed>     $value
     * @param array<string, mixed>        $rules
     * @param array<string, list<string>> $problems
     */
    private static function items(array $value, array $rules, string $at, array &$problems): void
    {
        $count = count($value);
        $least = $rules['minItems'] ?? null;
        $most = $rules['maxItems'] ?? null;

        if (is_int($least) && $count < $least) {
            $problems[$at][] = $count === 0
                ? sprintf('is empty, and wants %d or more. Leave the key out to send nothing.', $least)
                : sprintf('wants %d or more, and holds %d.', $least, $count);
        }

        // The one bound worth knowing before the call: a batch over it is refused whole, and the
        // fix is another call rather than another value.
        if (is_int($most) && $count > $most) {
            $problems[$at][] = sprintf('takes %d at a time, and holds %d. Send it in batches.', $most, $count);
        }

        /** @var array<string, mixed>|null $items */
        $items = is_array($rules['items'] ?? null) ? $rules['items'] : null;

        if ($items === null || $items === []) {
            return;
        }

        foreach ($value as $index => $one) {
            self::walk($one, $items, $at . '.' . $index, $problems);
        }
    }

    /**
     * @param array<array-key, mixed>     $value
     * @param array<string, mixed>        $rules
     * @param array<string, list<string>> $problems
     */
    private static function fields(array $value, array $rules, string $at, array &$problems): void
    {
        /** @var array<string, array<string, mixed>>|null $properties */
        $properties = is_array($rules['properties'] ?? null) ? $rules['properties'] : null;

        // An object the document describes without naming its fields takes anything, and the keys
        // are values in their own right rather than field names.
        if ($properties === null || $properties === []) {
            return;
        }

        /** @var list<string> $required */
        $required = is_array($rules['required'] ?? null) ? $rules['required'] : [];

        foreach ($required as $key) {
            if (!array_key_exists($key, $value)) {
                $problems[$at . '.' . $key][] = 'is required, and is not there.';
            }
        }

        foreach ($value as $key => $held) {
            $rule = $properties[$key] ?? null;

            // The whole point of the exercise: `first_nane` is a 422 from the API and a spelling
            // mistake here, and the document is the only thing that can tell the two apart.
            if ($rule === null) {
                $problems[$at . '.' . $key][] = sprintf(
                    'is not a field the document names here. It names %s.',
                    implode(', ', array_keys($properties)),
                );

                continue;
            }

            if ($rule !== []) {
                self::walk($held, $rule, $at . '.' . $key, $problems);
            }
        }
    }

    private static function holds(mixed $value, string $type): bool
    {
        return match ($type) {
            'string' => is_string($value),
            'integer' => is_int($value),
            'number' => is_int($value) || is_float($value),
            'boolean' => is_bool($value),
            'array' => is_array($value) && ($value === [] || array_is_list($value)),
            'object' => is_array($value) && ($value === [] || !array_is_list($value)),
            default => true,
        };
    }

    private static function wanted(string $type): string
    {
        return match ($type) {
            'string' => 'a string',
            'integer' => 'a whole number',
            'number' => 'a number',
            'boolean' => 'true or false',
            'array' => 'a list',
            'object' => 'an object',
            default => 'something else',
        };
    }

    private static function given(mixed $value): string
    {
        if (is_array($value)) {
            return $value === [] ? 'empty' : (array_is_list($value) ? 'a list' : 'an object');
        }

        return match (get_debug_type($value)) {
            'string' => 'a string',
            'int' => 'a whole number',
            'float' => 'a number',
            'bool' => 'true or false',
            'null' => 'null',
            default => 'neither',
        };
    }

    /** As it would be written in the code that sent it, so it can be found there. */
    private static function written(mixed $value): string
    {
        if (is_string($value)) {
            return "'" . $value . "'";
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return get_debug_type($value);
    }

    /**
     * A pattern the document publishes.
     *
     * `pattern` searches rather than matching the whole of a value, which is what JSON Schema says
     * it does; the ones this API publishes anchor themselves. A pattern this engine cannot compile
     * is left unchecked rather than reported as a failure nobody can act on.
     */
    private static function shaped(string $value, string $pattern): bool
    {
        return preg_match('#' . str_replace('#', '\\#', $pattern) . '#', $value) !== 0;
    }

    /** A real day rather than one that reads like one: the 31st of February is not a date. */
    private static function dated(string $value): bool
    {
        $held = DateTimeImmutable::createFromFormat('!Y-m-d', $value);

        return $held !== false && $held->format('Y-m-d') === $value;
    }

    private static function instant(string $value): bool
    {
        $shaped = '/^\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}(:\d{2})?(\.\d+)?(Z|[+-]\d{2}:?\d{2})?$/';

        return preg_match($shaped, $value) === 1 && strtotime($value) !== false;
    }
}
