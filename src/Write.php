<?php

declare(strict_types=1);

namespace Clockster;

/**
 * Building a body out of data that has holes in it.
 *
 * A write tells three things apart: a field you sent, a field you sent as null, and a field you did
 * not send at all. Null clears what is stored, and an absent key keeps it — see the README.
 *
 * Data from anywhere else has a fourth idea, and it is the empty string. A blank cell in a CSV, an
 * untouched input, a column somebody else's export leaves empty: all of them mean "nothing here"
 * rather than "store nothing here". Sent as they are, they overwrite the name, the email and the
 * phone number somebody typed into the web application with nothing at all, and there is no undoing
 * it. It is the one mistake this API makes easy and expensive, so here is the one line that avoids
 * it.
 */
final class Write
{
    /**
     * The same body without the keys holding an empty string.
     *
     * Null is kept: clearing a stored value is a thing you may well mean, and dropping it would
     * take away the only way to say so. `0`, `false` and `[]` are values too, and stay. Nested
     * arrays are walked, so one call covers a row, a batch of them, or a whole body — and an empty
     * string sitting in a list is dropped from it rather than left as a hole.
     *
     *     $clockster->users->upsert(['users' => [Write::filled([
     *         'external_id' => $row['external_id'],
     *         'first_name' => $row['first_name'],
     *         'role' => UsersRole::EMPLOYEE,
     *         'location_id' => $locations[$row['location_code']],
     *         'email' => $row['email'],   // blank in the file, so not sent, so not overwritten
     *     ])]]);
     *
     * A static analyser reads this as answering the shape it was given. That is right about every
     * key the shape marks optional and hopeful about the rest: dropping one the API requires is a
     * `422` this cannot see coming, and `Validator::check()` is what sees it.
     *
     * @template TBody of array<array-key, mixed>
     *
     * @param TBody $body
     *
     * @return TBody
     */
    public static function filled(array $body): array
    {
        $written = [];

        foreach ($body as $key => $value) {
            if ($value === '') {
                continue;
            }

            $written[$key] = is_array($value) ? self::filled($value) : $value;
        }

        /** @var TBody $answer a list keeps its numbering rather than the holes taking one out */
        $answer = array_is_list($body) ? array_values($written) : $written;

        return $answer;
    }
}
