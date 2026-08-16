<?php

declare(strict_types=1);

namespace Clockster\Generated;

use Clockster\Exception\ApiException;
use Clockster\Exception\TransportException;
use Clockster\Http\Caller;
use Generator;

/**
 * The operations of `$clockster->locations`.
 *
 * @phpstan-import-type LocationsDeleteResponse from Shapes
 * @phpstan-import-type LocationsGetResponse from Shapes
 * @phpstan-import-type LocationsListResponse from Shapes
 * @phpstan-import-type LocationsListRow from Shapes
 * @phpstan-import-type LocationsUpsertBody from Shapes
 * @phpstan-import-type LocationsUpsertResponse from Shapes
 */
final class Locations
{
    public function __construct(private readonly Caller $caller)
    {
    }

    /**
     * Delete a location.
     *
     * **Refused while anybody works there** — 409, `location_in_use` — counting both the
     * location on a person's record and the several they may also be assigned to.
     *
     * **Refused while anything sits beneath it** — 409, `location_has_children`. Deleting a
     * parent detaches its whole subtree and destroys the rows describing the ancestry, and
     * this API cannot express a hierarchy at all, so you would not be able to see what you
     * had taken apart.
     *
     * Once it goes, so do its managers, its device assignments and any auto-scheduler
     * configured for it; devices, schedules, tasks and approval routes keep working with the
     * location set to null. None of that is recoverable and none of it is logged.
     *
     * Move the people first, then delete.
     *
     * @param int $id The id of the row, as this API issued it.
     *
     * @return LocationsDeleteResponse
     *
     * @throws ApiException|TransportException
     */
    public function delete(int $id): array
    {
        /** @var LocationsDeleteResponse $answer */
        $answer = $this->caller->call(
            'DELETE',
            sprintf('/company/v3/locations/%d', $id),
        );

        return $answer;
    }

    /**
     * Read one location.
     *
     * The same keys the listing answers with. Somebody else's id is a `404`, where asking the
     * listing for it answers `200` with an empty array and leaves you counting.
     *
     * @param int $id The id of the row, as this API issued it.
     * @param list<string> $include Relations to load, comma-separated. Anything not named is absent
     * from the answer rather than null.
     *
     * @return LocationsGetResponse
     *
     * @throws ApiException|TransportException
     */
    public function get(int $id, array $include = []): array
    {
        /** @var LocationsGetResponse $answer */
        $answer = $this->caller->call(
            'GET',
            sprintf('/company/v3/locations/%d', $id),
            [
                'include' => $include,
            ],
        );

        return $answer;
    }

    /**
     * List locations.
     *
     * Ordered by `id` and paged on a cursor: no page number, no total, and nothing repeated
     * or skipped while the list is written to. A cursor issued for another ordering is
     * refused rather than silently restarting the list.
     *
     * Coordinates are numbers, and a latitude of exactly 0 is a coordinate rather than a
     * missing one.
     *
     * `include=managers` adds the employees who manage the location, as on departments and
     * user filters.
     *
     * @param int|null $perPage How many rows one page holds. Defaults to 50.
     * @param string|null $cursor The `meta.next_cursor` of the previous page. Omit it for the first. A cursor is bound to the filters it was issued under — change them and start again.
     * @param string|null $search Free text over the names the section lists.
     * @param list<string> $include Relations to load, comma-separated. Anything not named is absent
     * from the answer rather than null.
     * @param list<string> $codes Only these employee codes.
     * @param string|null $updatedSince Only rows changed at or after this instant (ISO 8601). The cheap way to sync: ask for what moved, not for everything.
     *
     * @return LocationsListResponse
     *
     * @throws ApiException|TransportException
     */
    public function list(
        ?int $perPage = null,
        ?string $cursor = null,
        ?string $search = null,
        array $include = [],
        array $codes = [],
        ?string $updatedSince = null,
    ): array {
        /** @var LocationsListResponse $answer */
        $answer = $this->caller->call(
            'GET',
            '/company/v3/locations',
            [
                'per_page' => $perPage,
                'cursor' => $cursor,
                'search' => $search,
                'include' => $include,
                'codes' => $codes,
                'updated_since' => $updatedSince,
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
     * @param list<string> $include Relations to load, comma-separated. Anything not named is absent
     * from the answer rather than null.
     * @param list<string> $codes Only these employee codes.
     * @param string|null $updatedSince Only rows changed at or after this instant (ISO 8601). The cheap way to sync: ask for what moved, not for everything.
     *
     * @return \Generator<int, LocationsListRow>
     *
     * @throws ApiException|TransportException
     */
    public function listAll(
        ?int $perPage = null,
        ?string $search = null,
        array $include = [],
        array $codes = [],
        ?string $updatedSince = null,
    ): Generator {
        $cursor = null;
        $seen = [];

        while (true) {
            $page = $this->list(
                perPage: $perPage,
                search: $search,
                include: $include,
                codes: $codes,
                updatedSince: $updatedSince,
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
     * Create or update locations.
     *
     * Up to 100 entries in one call, matched on `external_id`.
     *
     * **Not matched on the name.** Renaming an entry on your side updates ours, where matching
     * on `title` would have created a second and orphaned the first.
     *
     * `code` and `external_id` are different things and both are kept: `code` is a label you
     * fill in and we never validate, `external_id` is what the match runs on. Coordinates and
     * radius are set here too.
     *
     * @param LocationsUpsertBody $body
     *
     * @return LocationsUpsertResponse
     *
     * @throws ApiException|TransportException
     */
    public function upsert(array $body): array
    {
        /** @var LocationsUpsertResponse $answer */
        $answer = $this->caller->call(
            'POST',
            '/company/v3/locations/upsert',
            body: $body,
        );

        return $answer;
    }
}
