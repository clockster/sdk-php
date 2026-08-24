<?php

declare(strict_types=1);

namespace Clockster\Generated;

use Clockster\Exception\ApiException;
use Clockster\Exception\InvalidBodyException;
use Clockster\Exception\TransportException;
use Clockster\Http\Caller;
use Generator;

/**
 * The operations of `$clockster->departments`.
 *
 * @phpstan-import-type DepartmentsDeleteResponse from Shapes
 * @phpstan-import-type DepartmentsGetResponse from Shapes
 * @phpstan-import-type DepartmentsInclude from Shapes
 * @phpstan-import-type DepartmentsListResponse from Shapes
 * @phpstan-import-type DepartmentsListRow from Shapes
 * @phpstan-import-type DepartmentsUpsertBody from Shapes
 * @phpstan-import-type DepartmentsUpsertResponse from Shapes
 */
final class Departments
{
    public function __construct(private readonly Caller $caller)
    {
    }

    /**
     * Delete a department.
     *
     * **Refused while anybody is in it** — 409, `department_in_use`.
     *
     * Once it goes, so do its managers, and there is no way to reconstruct who was in it.
     *
     * Move the people first, then delete. Somebody dismissed does not count as being in it.
     *
     * @param int $id The id of the row, as this API issued it.
     *
     * @return DepartmentsDeleteResponse
     *
     * @throws ApiException|TransportException
     */
    public function delete(int $id): array
    {
        /** @var DepartmentsDeleteResponse $answer */
        $answer = $this->caller->call(
            'DELETE',
            sprintf('/company/v3/departments/%d', $id),
        );

        return $answer;
    }

    /**
     * Read one department.
     *
     * The same keys and the same `include` vocabulary as the listing — `include=managers` adds
     * the managers. Somebody else's id is a `404`.
     *
     * @param int $id The id of the row, as this API issued it.
     * @param list<DepartmentsInclude> $include Relations to load, comma-separated. Anything not
     * named is absent from the answer rather than null.
     *
     * @return DepartmentsGetResponse
     *
     * @throws ApiException|TransportException
     */
    public function get(int $id, array $include = []): array
    {
        /** @var DepartmentsGetResponse $answer */
        $answer = $this->caller->call(
            'GET',
            sprintf('/company/v3/departments/%d', $id),
            [
                'include' => $include,
            ],
        );

        return $answer;
    }

    /**
     * List departments.
     *
     * `include=managers` adds the managers. Without it the key is absent, never null standing in
     * for "not asked for".
     *
     * @param int|null $perPage How many rows one page holds. Defaults to 50.
     * @param string|null $cursor The `meta.next_cursor` of the previous page. Omit it for the first. A cursor is bound to the filters it was issued under — change them and start again.
     * @param string|null $search Free text over the names the section lists.
     * @param string|null $updatedSince Only rows changed at or after this instant (ISO 8601). The cheap way to sync: ask for what moved, not for everything.
     * @param list<DepartmentsInclude> $include Relations to load, comma-separated. Anything not
     * named is absent from the answer rather than null.
     *
     * @return DepartmentsListResponse
     *
     * @throws ApiException|TransportException
     */
    public function list(
        ?int $perPage = null,
        ?string $cursor = null,
        ?string $search = null,
        ?string $updatedSince = null,
        array $include = [],
    ): array {
        /** @var DepartmentsListResponse $answer */
        $answer = $this->caller->call(
            'GET',
            '/company/v3/departments',
            [
                'per_page' => $perPage,
                'cursor' => $cursor,
                'search' => $search,
                'updated_since' => $updatedSince,
                'include' => $include,
            ],
        );

        return $answer;
    }

    /**
     * Every row of list(), a page at a time.
     *
     * A refused page is thrown where it was refused, so half a listing is never mistaken for the
     * whole of one. A cursor belongs to the filters it was issued under: change them and walk
     * again.
     *
     * @param int|null $perPage How many rows one page holds. Defaults to 50.
     * @param string|null $search Free text over the names the section lists.
     * @param string|null $updatedSince Only rows changed at or after this instant (ISO 8601). The cheap way to sync: ask for what moved, not for everything.
     * @param list<DepartmentsInclude> $include Relations to load, comma-separated. Anything not
     * named is absent from the answer rather than null.
     *
     * @return \Generator<int, DepartmentsListRow>
     *
     * @throws ApiException|TransportException
     */
    public function listAll(
        ?int $perPage = null,
        ?string $search = null,
        ?string $updatedSince = null,
        array $include = [],
    ): Generator {
        $cursor = null;
        $seen = [];

        while (true) {
            $page = $this->list(
                perPage: $perPage,
                search: $search,
                updatedSince: $updatedSince,
                include: $include,
                cursor: $cursor,
            );

            foreach ($page['data'] as $row) {
                yield $row;
            }

            $cursor = $page['meta']['next_cursor'] ?? null;

            // A cursor that repeats would page until the process is killed, which is worse
            // than stopping.
            if ($cursor === null || isset($seen[$cursor])) {
                return;
            }

            $seen[$cursor] = true;
        }
    }

    /**
     * Create or update departments.
     *
     * Up to 100 entries in one call, matched on `external_id`.
     *
     * **Not matched on the name.** Renaming an entry on your side updates ours, where matching
     * on `title` would have created a second and orphaned the first.
     *
     * What each field is:
     *
     * - `items` — The rows to write. Each carries your own `external_id`, and a row already
     *   stored under that key is updated rather than added.
     * - `items[].external_id` — Your own key for this row. Send it on every write and the next
     *   one updates rather than duplicates.
     * - `items[].title` — The name this is shown under.
     * - `items[].description` — Free text about this row, for people rather than for your code.
     *
     * @param DepartmentsUpsertBody $body
     *
     * @return DepartmentsUpsertResponse
     *
     * @throws ApiException|InvalidBodyException|TransportException
     */
    public function upsert(array $body): array
    {
        /** @var DepartmentsUpsertResponse $answer */
        $answer = $this->caller->call(
            'POST',
            '/company/v3/departments/upsert',
            body: $body,
        );

        return $answer;
    }
}
