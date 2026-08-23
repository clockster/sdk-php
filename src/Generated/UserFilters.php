<?php

declare(strict_types=1);

namespace Clockster\Generated;

use Clockster\Exception\ApiException;
use Clockster\Exception\InvalidBodyException;
use Clockster\Exception\TransportException;
use Clockster\Http\Caller;
use Generator;

/**
 * The operations of `$clockster->userFilters`.
 *
 * @phpstan-import-type UserFiltersDeleteResponse from Shapes
 * @phpstan-import-type UserFiltersGetResponse from Shapes
 * @phpstan-import-type UserFiltersInclude from Shapes
 * @phpstan-import-type UserFiltersListResponse from Shapes
 * @phpstan-import-type UserFiltersListRow from Shapes
 * @phpstan-import-type UserFiltersUpsertBody from Shapes
 * @phpstan-import-type UserFiltersUpsertResponse from Shapes
 */
final class UserFilters
{
    public function __construct(private readonly Caller $caller)
    {
    }

    /**
     * Delete a user filter.
     *
     * Members do not block it — a filter is a label, and a caller who keeps their filters in
     * step re-creates it on the next sync.
     *
     * **Refused while an approval route points at it** — 409, `user_filter_in_use`. The route
     * would survive with nobody to approve through it and quietly stop routing. Change the
     * route first.
     *
     * @param int $id The id of the row, as this API issued it.
     *
     * @return UserFiltersDeleteResponse
     *
     * @throws ApiException|TransportException
     */
    public function delete(int $id): array
    {
        /** @var UserFiltersDeleteResponse $answer */
        $answer = $this->caller->call(
            'DELETE',
            sprintf('/company/v3/user-filters/%d', $id),
        );

        return $answer;
    }

    /**
     * Read one user filter.
     *
     * The same keys and the same `include` vocabulary as the listing. Somebody else's id is a
     * `404`.
     *
     * @param int $id The id of the row, as this API issued it.
     * @param list<UserFiltersInclude> $include Relations to load, comma-separated. Anything not
     * named is absent from the answer rather than null.
     *
     * @return UserFiltersGetResponse
     *
     * @throws ApiException|TransportException
     */
    public function get(int $id, array $include = []): array
    {
        /** @var UserFiltersGetResponse $answer */
        $answer = $this->caller->call(
            'GET',
            sprintf('/company/v3/user-filters/%d', $id),
            [
                'include' => $include,
            ],
        );

        return $answer;
    }

    /**
     * List user filters.
     *
     * `include=managers` adds the managers, as on departments.
     *
     * @param int|null $perPage How many rows one page holds. Defaults to 50.
     * @param string|null $cursor The `meta.next_cursor` of the previous page. Omit it for the first. A cursor is bound to the filters it was issued under — change them and start again.
     * @param string|null $search Free text over the names the section lists.
     * @param string|null $updatedSince Only rows changed at or after this instant (ISO 8601). The cheap way to sync: ask for what moved, not for everything.
     * @param list<UserFiltersInclude> $include Relations to load, comma-separated. Anything not
     * named is absent from the answer rather than null.
     *
     * @return UserFiltersListResponse
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
        /** @var UserFiltersListResponse $answer */
        $answer = $this->caller->call(
            'GET',
            '/company/v3/user-filters',
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
     * @param list<UserFiltersInclude> $include Relations to load, comma-separated. Anything not
     * named is absent from the answer rather than null.
     *
     * @return \Generator<int, UserFiltersListRow>
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
     * Create or update user filters.
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
     * @param UserFiltersUpsertBody $body
     *
     * @return UserFiltersUpsertResponse
     *
     * @throws ApiException|InvalidBodyException|TransportException
     */
    public function upsert(array $body): array
    {
        /** @var UserFiltersUpsertResponse $answer */
        $answer = $this->caller->call(
            'POST',
            '/company/v3/user-filters/upsert',
            body: $body,
        );

        return $answer;
    }
}
