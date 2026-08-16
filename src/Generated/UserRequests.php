<?php

declare(strict_types=1);

namespace Clockster\Generated;

use Clockster\Exception\ApiException;
use Clockster\Exception\TransportException;
use Clockster\Http\Caller;
use Generator;

/**
 * The operations of `$clockster->userRequests`.
 *
 * @phpstan-import-type UserRequestsGetResponse from Shapes
 * @phpstan-import-type UserRequestsListResponse from Shapes
 * @phpstan-import-type UserRequestsListRow from Shapes
 */
final class UserRequests
{
    public function __construct(private readonly Caller $caller)
    {
    }

    /**
     * Read one request.
     *
     * Answers with `content`, unlike the listing: one row is one shape to make sense of, not a
     * hundred.
     *
     * @param int $id The id of the row, as this API issued it.
     *
     * @return UserRequestsGetResponse
     *
     * @throws ApiException|TransportException
     */
    public function get(int $id): array
    {
        /** @var UserRequestsGetResponse $answer */
        $answer = $this->caller->call(
            'GET',
            sprintf('/company/v3/user-requests/%d', $id),
        );

        return $answer;
    }

    /**
     * List requests.
     *
     * What people asked for, and what became of it — leave, schedule changes, corrections and
     * money.
     *
     * **Read this to learn that a timesheet moved.** Half of everything here is a batch
     * clock-in correction: somebody forgot to punch, a manager approved the fix, and the
     * attendance for those days changed after the fact. Only 48 per cent of those are approved
     * within three days of the day they correct, and a third reach back more than a week — so
     * a caller that pulled a timesheet last week cannot assume it still holds. Attendance
     * carries no timestamps of its own, which makes this listing paged on `updated_since` the
     * only signal that anything has moved.
     *
     * `period` is the field that makes that usable: the span of days a request concerns,
     * wherever its kind happens to keep them. A clock-in correction keeps them inside the
     * punches, a leave request as a period or a list, a request for a certificate not at all —
     * both ends are null there rather than invented.
     *
     * `subtype` is the second half of `type`, and the product keeps it in two different places
     * — `content.type` for most kinds, `content.leave_type` for leave. It is answered as one
     * field, and `subtypes` filters on both.
     *
     * `comment` is what the author wrote when filing it, ordinarily the reason. Comments the
     * workflow writes itself — on acknowledgement, or when a spawned task closes — are not
     * answered here.
     *
     * **Oldest first.** A first call lands on the earliest request this company ever filed,
     * which for a long-standing one is years back. That order is what lets a full export
     * finish in one walk, and it is not what you want for "what changed lately": ask with
     * `updated_since`.
     *
     * `content` is behind `include=content`: its shape depends on the kind, and one schema
     * describes one shape everywhere else on this surface.
     *
     * **Reading only.** Creating a request enters a workflow — approval routes resolve,
     * approvers are notified, tasks are spawned — and approving one is a person's decision that
     * a dismissal application or a sick note gives weight to.
     *
     * @param int|null $perPage How many rows one page holds. Defaults to 50.
     * @param string|null $cursor The `meta.next_cursor` of the previous page. Omit it for the first. A cursor is bound to the filters it was issued under — change them and start again.
     * @param list<string> $types Only rows of these types.
     * @param list<string> $statuses Only rows in these states.
     * @param list<string> $subtypes Only rows of these subtypes, which narrow a type further.
     * @param list<int> $users Only rows belonging to these people, by id.
     * @param string|null $updatedSince Only rows changed at or after this instant (ISO 8601). The cheap way to sync: ask for what moved, not for everything.
     * @param list<string> $include Relations to load, comma-separated. Anything not named is absent
     * from the answer rather than null.
     *
     * @return UserRequestsListResponse
     *
     * @throws ApiException|TransportException
     */
    public function list(
        ?int $perPage = null,
        ?string $cursor = null,
        array $types = [],
        array $statuses = [],
        array $subtypes = [],
        array $users = [],
        ?string $updatedSince = null,
        array $include = [],
    ): array {
        /** @var UserRequestsListResponse $answer */
        $answer = $this->caller->call(
            'GET',
            '/company/v3/user-requests',
            [
                'per_page' => $perPage,
                'cursor' => $cursor,
                'types' => $types,
                'statuses' => $statuses,
                'subtypes' => $subtypes,
                'users' => $users,
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
     * @param list<string> $types Only rows of these types.
     * @param list<string> $statuses Only rows in these states.
     * @param list<string> $subtypes Only rows of these subtypes, which narrow a type further.
     * @param list<int> $users Only rows belonging to these people, by id.
     * @param string|null $updatedSince Only rows changed at or after this instant (ISO 8601). The cheap way to sync: ask for what moved, not for everything.
     * @param list<string> $include Relations to load, comma-separated. Anything not named is absent
     * from the answer rather than null.
     *
     * @return \Generator<int, UserRequestsListRow>
     *
     * @throws ApiException|TransportException
     */
    public function listAll(
        ?int $perPage = null,
        array $types = [],
        array $statuses = [],
        array $subtypes = [],
        array $users = [],
        ?string $updatedSince = null,
        array $include = [],
    ): Generator {
        $cursor = null;
        $seen = [];

        while (true) {
            $page = $this->list(
                perPage: $perPage,
                types: $types,
                statuses: $statuses,
                subtypes: $subtypes,
                users: $users,
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
}
