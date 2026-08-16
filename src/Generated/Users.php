<?php

declare(strict_types=1);

namespace Clockster\Generated;

use Clockster\Exception\ApiException;
use Clockster\Exception\TransportException;
use Clockster\Http\Caller;
use Generator;

/**
 * The operations of `$clockster->users`.
 *
 * @phpstan-import-type UsersDismissBody from Shapes
 * @phpstan-import-type UsersDismissResponse from Shapes
 * @phpstan-import-type UsersGetResponse from Shapes
 * @phpstan-import-type UsersListResponse from Shapes
 * @phpstan-import-type UsersListRow from Shapes
 * @phpstan-import-type UsersUpsertBody from Shapes
 * @phpstan-import-type UsersUpsertResponse from Shapes
 */
final class Users
{
    public function __construct(private readonly Caller $caller)
    {
    }

    /**
     * Dismiss employees.
     *
     * Up to 100 people in one call, each named by `external_id` or by `id`, exactly one per
     * item.
     *
     * **This is dismissal, not erasure.** The record stays and stays readable: the person
     * appears under `status=dismissed` with `dismissed_at` set, and the seat is freed for
     * somebody else. It is the shape leaving actually has — a nightly sync noticing that
     * twelve people are no longer on the roster.
     *
     * **There is no hard delete on this API.** Erasing a person takes their attendance,
     * payroll, documents and bank details with them, with no way to undo it, and one ability
     * grants this whole API — an integrator that syncs your roster cannot be given that
     * without also being given erasure. If a retention obligation needs it, ask us.
     *
     * **Somebody already gone answers `already_dismissed`** rather than failing, which matters
     * here because dismissing frees the key for a new hire.
     *
     * **If a key is held by two people** — one who left and one hired since — the living one
     * is the one dismissed.
     *
     * What happens that you cannot see, and cannot undo:
     *
     * - Their sessions end immediately and their phone is unpaired.
     * - Every terminal at their locations is told to forget their face. That is sent once and
     *   not retried, so a terminal that is offline at the time keeps admitting them.
     * - **Requests still waiting on them to approve are cancelled**, not just their own. Dismiss
     *   a manager and their team's pending vacation requests are cancelled with them.
     * - An offboarding process starts, if the company has one configured.
     * - `date_leave` is filled in with today's date if it was empty.
     *
     * @param UsersDismissBody $body
     *
     * @return UsersDismissResponse
     *
     * @throws ApiException|TransportException
     */
    public function dismiss(array $body): array
    {
        /** @var UsersDismissResponse $answer */
        $answer = $this->caller->call(
            'POST',
            '/company/v3/users/dismiss',
            body: $body,
        );

        return $answer;
    }

    /**
     * Read one employee.
     *
     * One employee by our id. Prefer it over `?ids=` when you expect exactly one: someone who
     * is not yours answers `404`, where the listing answers `200` with an empty array, so a
     * status can be branched on without counting.
     *
     * Reachable for a dismissed person too.
     *
     * @param int $id The id of the row, as this API issued it.
     * @param list<string> $include Relations to load, comma-separated. Anything not named is absent
     * from the answer rather than null.
     *
     * @return UsersGetResponse
     *
     * @throws ApiException|TransportException
     */
    public function get(int $id, array $include = []): array
    {
        /** @var UsersGetResponse $answer */
        $answer = $this->caller->call(
            'GET',
            sprintf('/company/v3/users/%d', $id),
            [
                'include' => $include,
            ],
        );

        return $answer;
    }

    /**
     * List employees.
     *
     * The roster as we hold it. Every scalar is always present; a relation appears only when
     * `include` names it.
     *
     * **`status` decides whether the people who left are in the answer** — `active` by
     * default, `dismissed` for only them, `all` for both. A dismissed employee keeps their
     * record: `date_leave` says when they were let go and `dismissed_at` when the record was
     * closed. A sync that never asks for them cannot learn that anyone left, so ask
     * periodically even if your day-to-day reads are `active`.
     *
     * **`external_ids` is the other half of the roster write.** Ask with the same keys you
     * sent to `/users/upsert` and reconcile without keeping a map of our ids.
     *
     * `updated_since` reads only what changed, against the `updated_at` every row carries.
     *
     * @param int|null $perPage How many rows one page holds. Defaults to 50.
     * @param string|null $cursor The `meta.next_cursor` of the previous page. Omit it for the first. A cursor is bound to the filters it was issued under — change them and start again.
     * @param string|null $search Free text over the names the section lists.
     * @param string|null $updatedSince Only rows changed at or after this instant (ISO 8601). The cheap way to sync: ask for what moved, not for everything.
     * @param string|null $status Whether the people who left are in the answer: `active`, `dismissed`, or `all`.
     * @param list<int> $ids Only these ids.
     * @param list<string> $codes Only these employee codes.
     * @param list<string> $externalIds Only rows carrying these keys of yours. The other half of an
     * upsert: write with your key, read back with it.
     * @param list<int> $locations Only these locations, by id.
     * @param list<int> $departments Only these departments, by id.
     * @param list<int> $positions Only these positions, by id.
     * @param list<int> $userFilters Only these user filters, by id.
     * @param list<string> $employment Only people on these employment terms.
     * @param list<string> $include Relations to load, comma-separated. Anything not named is absent
     * from the answer rather than null.
     *
     * @return UsersListResponse
     *
     * @throws ApiException|TransportException
     */
    public function list(
        ?int $perPage = null,
        ?string $cursor = null,
        ?string $search = null,
        ?string $updatedSince = null,
        ?string $status = null,
        array $ids = [],
        array $codes = [],
        array $externalIds = [],
        array $locations = [],
        array $departments = [],
        array $positions = [],
        array $userFilters = [],
        array $employment = [],
        array $include = [],
    ): array {
        /** @var UsersListResponse $answer */
        $answer = $this->caller->call(
            'GET',
            '/company/v3/users',
            [
                'per_page' => $perPage,
                'cursor' => $cursor,
                'search' => $search,
                'updated_since' => $updatedSince,
                'status' => $status,
                'ids' => $ids,
                'codes' => $codes,
                'external_ids' => $externalIds,
                'locations' => $locations,
                'departments' => $departments,
                'positions' => $positions,
                'user_filters' => $userFilters,
                'employment' => $employment,
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
     * @param string|null $status Whether the people who left are in the answer: `active`, `dismissed`, or `all`.
     * @param list<int> $ids Only these ids.
     * @param list<string> $codes Only these employee codes.
     * @param list<string> $externalIds Only rows carrying these keys of yours. The other half of an
     * upsert: write with your key, read back with it.
     * @param list<int> $locations Only these locations, by id.
     * @param list<int> $departments Only these departments, by id.
     * @param list<int> $positions Only these positions, by id.
     * @param list<int> $userFilters Only these user filters, by id.
     * @param list<string> $employment Only people on these employment terms.
     * @param list<string> $include Relations to load, comma-separated. Anything not named is absent
     * from the answer rather than null.
     *
     * @return \Generator<int, UsersListRow>
     *
     * @throws ApiException|TransportException
     */
    public function listAll(
        ?int $perPage = null,
        ?string $search = null,
        ?string $updatedSince = null,
        ?string $status = null,
        array $ids = [],
        array $codes = [],
        array $externalIds = [],
        array $locations = [],
        array $departments = [],
        array $positions = [],
        array $userFilters = [],
        array $employment = [],
        array $include = [],
    ): Generator {
        $cursor = null;
        $seen = [];

        while (true) {
            $page = $this->list(
                perPage: $perPage,
                search: $search,
                updatedSince: $updatedSince,
                status: $status,
                ids: $ids,
                codes: $codes,
                externalIds: $externalIds,
                locations: $locations,
                departments: $departments,
                positions: $positions,
                userFilters: $userFilters,
                employment: $employment,
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
     * Create or update employees.
     *
     * The roster, written in batches of up to 100. `external_id` is optional here and behaves
     * as it does everywhere: an item carrying one updates the person it names, an item without
     * one always creates. `first_name`, `role` and `location_id` are the minimum.
     *
     * The field set is what an HR system holds about an employee.
     *
     * A person created here reaches the turnstiles of their location, and every location's
     * devices are told once for the whole batch rather than once per person.
     *
     * @param UsersUpsertBody $body
     *
     * @return UsersUpsertResponse
     *
     * @throws ApiException|TransportException
     */
    public function upsert(array $body): array
    {
        /** @var UsersUpsertResponse $answer */
        $answer = $this->caller->call(
            'POST',
            '/company/v3/users/upsert',
            body: $body,
        );

        return $answer;
    }
}
