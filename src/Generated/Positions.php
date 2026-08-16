<?php

declare(strict_types=1);

namespace Clockster\Generated;

use Clockster\Exception\ApiException;
use Clockster\Exception\TransportException;
use Clockster\Http\Caller;
use Generator;

/**
 * The operations of `$clockster->positions`.
 *
 * @phpstan-import-type PositionsDeleteResponse from Shapes
 * @phpstan-import-type PositionsGetResponse from Shapes
 * @phpstan-import-type PositionsListResponse from Shapes
 * @phpstan-import-type PositionsListRow from Shapes
 * @phpstan-import-type PositionsUpsertBody from Shapes
 * @phpstan-import-type PositionsUpsertResponse from Shapes
 */
final class Positions
{
    public function __construct(private readonly Caller $caller)
    {
    }

    /**
     * Delete a position.
     *
     * **Refused while anybody holds it** — 409, `position_in_use`.
     *
     * Beyond the people: deleting a position destroys its auto-scheduler staffing
     * configuration outright, so a rota that says "two bakers on nights" stops saying it.
     *
     * Move the people first, then delete.
     *
     * @param int $id The id of the row, as this API issued it.
     *
     * @return PositionsDeleteResponse
     *
     * @throws ApiException|TransportException
     */
    public function delete(int $id): array
    {
        /** @var PositionsDeleteResponse $answer */
        $answer = $this->caller->call(
            'DELETE',
            sprintf('/company/v3/positions/%d', $id),
        );

        return $answer;
    }

    /**
     * Read one position.
     *
     * The same keys the listing answers with. A position carries no managers, so asking to include
     * them is refused rather than answered with an empty list.
     *
     * @param int $id The id of the row, as this API issued it.
     * @param list<string> $include Relations to load, comma-separated. Anything not named is absent
     * from the answer rather than null.
     *
     * @return PositionsGetResponse
     *
     * @throws ApiException|TransportException
     */
    public function get(int $id, array $include = []): array
    {
        /** @var PositionsGetResponse $answer */
        $answer = $this->caller->call(
            'GET',
            sprintf('/company/v3/positions/%d', $id),
            [
                'include' => $include,
            ],
        );

        return $answer;
    }

    /**
     * List positions.
     *
     * A position carries no managers, so asking to include them is refused rather than answered
     * with an empty list.
     *
     * @param int|null $perPage How many rows one page holds. Defaults to 50.
     * @param string|null $cursor The `meta.next_cursor` of the previous page. Omit it for the first. A cursor is bound to the filters it was issued under — change them and start again.
     * @param string|null $search Free text over the names the section lists.
     * @param string|null $updatedSince Only rows changed at or after this instant (ISO 8601). The cheap way to sync: ask for what moved, not for everything.
     * @param list<string> $include Relations to load, comma-separated. Anything not named is absent
     * from the answer rather than null.
     *
     * @return PositionsListResponse
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
        /** @var PositionsListResponse $answer */
        $answer = $this->caller->call(
            'GET',
            '/company/v3/positions',
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
     * @param list<string> $include Relations to load, comma-separated. Anything not named is absent
     * from the answer rather than null.
     *
     * @return \Generator<int, PositionsListRow>
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
     * Create or update positions.
     *
     * Up to 100 entries in one call, matched on `external_id`.
     *
     * **Not matched on the name.** Renaming an entry on your side updates ours, where matching
     * on `title` would have created a second and orphaned the first.
     *
     * @param PositionsUpsertBody $body
     *
     * @return PositionsUpsertResponse
     *
     * @throws ApiException|TransportException
     */
    public function upsert(array $body): array
    {
        /** @var PositionsUpsertResponse $answer */
        $answer = $this->caller->call(
            'POST',
            '/company/v3/positions/upsert',
            body: $body,
        );

        return $answer;
    }
}
