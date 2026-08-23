<?php

declare(strict_types=1);

namespace Clockster\Generated;

use Clockster\Exception\ApiException;
use Clockster\Exception\InvalidBodyException;
use Clockster\Exception\TransportException;
use Clockster\Http\Caller;
use Generator;

/**
 * The operations of `$clockster->tasks`.
 *
 * @phpstan-import-type TasksGetResponse from Shapes
 * @phpstan-import-type TasksInclude from Shapes
 * @phpstan-import-type TasksListResponse from Shapes
 * @phpstan-import-type TasksListRow from Shapes
 * @phpstan-import-type TasksStatus from Shapes
 * @phpstan-import-type TasksUpsertBody from Shapes
 * @phpstan-import-type TasksUpsertResponse from Shapes
 */
final class Tasks
{
    public function __construct(private readonly Caller $caller)
    {
    }

    /**
     * Read one task.
     *
     * The same keys the listing answers with, and the same `include` vocabulary. Another company's
     * id is a `404`.
     *
     * @param int $id The id of the row, as this API issued it.
     * @param list<TasksInclude> $include Relations to load, comma-separated. Anything not named is
     * absent from the answer rather than null.
     *
     * @return TasksGetResponse
     *
     * @throws ApiException|TransportException
     */
    public function get(int $id, array $include = []): array
    {
        /** @var TasksGetResponse $answer */
        $answer = $this->caller->call(
            'GET',
            sprintf('/company/v3/tasks/%d', $id),
            [
                'include' => $include,
            ],
        );

        return $answer;
    }

    /**
     * List tasks.
     *
     * Work as we hold it: what was issued, what became of it, and how it measured up.
     *
     * `kpi_fact` against `kpi_plan`, plus `time_worked`, are the point of reading a task back
     * — what was asked for, what was achieved, how long it took. `status` says where it got
     * to.
     *
     * **Twenty-four fields, not the forty the table has.** Eight of the rest configure how the
     * mobile application behaves while the job is done — whether it demands a photo, records a
     * location, keeps the steps in order. That is a task template's business, and neither
     * useful nor settable here.
     *
     * `include=items` adds the steps, `include=managers` the people who approve or are
     * notified. Without them the keys are absent, never null standing in for "not asked for".
     *
     * **`updated_since` is what an export should page on**, as an instant: a task moves
     * through its statuses inside a working day, so a caller polling for completions needs to
     * say which minute.
     *
     * `statuses` takes `created`, `started`, `paused`, `completed`, `incompleted` and
     * `pastdue`. Three more exist in the database and none is offered: `finished` and
     * `unfinished` are deprecated spellings, and `pending` is reached only through approval.
     * A value that cannot be explained is worse than one that is absent.
     *
     * **Oldest first.** A first call lands on the earliest task this company ever issued,
     * which for a long-standing one is years back. That order is what lets a full export
     * finish in one walk, and it is not what you want for "what happened lately": ask with
     * `updated_since`, or narrow with `due_from` and `due_to`.
     *
     * @param int|null $perPage How many rows one page holds. Defaults to 50.
     * @param string|null $cursor The `meta.next_cursor` of the previous page. Omit it for the first. A cursor is bound to the filters it was issued under — change them and start again.
     * @param list<string> $externalIds Only rows carrying these keys of yours. The other half of an
     * upsert: write with your key, read back with it.
     * @param list<int> $users Only rows belonging to these people, by id.
     * @param list<int> $categories Only rows in these categories.
     * @param list<TasksStatus> $statuses Only rows in these states.
     * @param bool|null $active Only rows switched on (`true`) or off (`false`). Omit for both.
     * @param string|null $search Free text over the names the section lists.
     * @param string|null $dueFrom Due at or after this date (YYYY-MM-DD).
     * @param string|null $dueTo Due at or before this date (YYYY-MM-DD).
     * @param string|null $updatedSince Only rows changed at or after this instant (ISO 8601). The cheap way to sync: ask for what moved, not for everything.
     * @param list<TasksInclude> $include Relations to load, comma-separated. Anything not named is
     * absent from the answer rather than null.
     *
     * @return TasksListResponse
     *
     * @throws ApiException|TransportException
     */
    public function list(
        ?int $perPage = null,
        ?string $cursor = null,
        array $externalIds = [],
        array $users = [],
        array $categories = [],
        array $statuses = [],
        ?bool $active = null,
        ?string $search = null,
        ?string $dueFrom = null,
        ?string $dueTo = null,
        ?string $updatedSince = null,
        array $include = [],
    ): array {
        /** @var TasksListResponse $answer */
        $answer = $this->caller->call(
            'GET',
            '/company/v3/tasks',
            [
                'per_page' => $perPage,
                'cursor' => $cursor,
                'external_ids' => $externalIds,
                'users' => $users,
                'categories' => $categories,
                'statuses' => $statuses,
                'active' => $active,
                'search' => $search,
                'due_from' => $dueFrom,
                'due_to' => $dueTo,
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
     * @param list<string> $externalIds Only rows carrying these keys of yours. The other half of an
     * upsert: write with your key, read back with it.
     * @param list<int> $users Only rows belonging to these people, by id.
     * @param list<int> $categories Only rows in these categories.
     * @param list<TasksStatus> $statuses Only rows in these states.
     * @param bool|null $active Only rows switched on (`true`) or off (`false`). Omit for both.
     * @param string|null $search Free text over the names the section lists.
     * @param string|null $dueFrom Due at or after this date (YYYY-MM-DD).
     * @param string|null $dueTo Due at or before this date (YYYY-MM-DD).
     * @param string|null $updatedSince Only rows changed at or after this instant (ISO 8601). The cheap way to sync: ask for what moved, not for everything.
     * @param list<TasksInclude> $include Relations to load, comma-separated. Anything not named is
     * absent from the answer rather than null.
     *
     * @return \Generator<int, TasksListRow>
     *
     * @throws ApiException|TransportException
     */
    public function listAll(
        ?int $perPage = null,
        array $externalIds = [],
        array $users = [],
        array $categories = [],
        array $statuses = [],
        ?bool $active = null,
        ?string $search = null,
        ?string $dueFrom = null,
        ?string $dueTo = null,
        ?string $updatedSince = null,
        array $include = [],
    ): Generator {
        $cursor = null;
        $seen = [];

        while (true) {
            $page = $this->list(
                perPage: $perPage,
                externalIds: $externalIds,
                users: $users,
                categories: $categories,
                statuses: $statuses,
                active: $active,
                search: $search,
                dueFrom: $dueFrom,
                dueTo: $dueTo,
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
     * Issue tasks.
     *
     * Up to 100 pieces of work in one call, matched on `external_id`, which is required here
     * — a task has no natural key of its own, since the same round is issued every week under
     * the same title.
     *
     * **`status` is not accepted, and neither are the timestamps around it.** The product
     * moves a task through its lifecycle with events — completing notifies, approval routes,
     * reopening makes it pastdue again — and a status written straight onto the row fires none
     * of that. Issue the work here; read where it got to with `GET /company/v3/tasks`.
     *
     * **Eight fields are not accepted either** — `req_photo`, `req_sequence`, `gallery`,
     * `get_location`, `get_timing`, `is_keep_status`, `req_approve`, `req_notify`. They
     * configure the mobile application, not the job.
     *
     * **Where the work sits is taken from whoever it is for.** Omit `location_id`,
     * `department_id` and `position_id` and they come from the assignee — your system knows
     * the person, not our org chart. Send them to override.
     *
     * **`items` is an exception to the omitted-field rule**: sending it replaces the steps
     * outright, because a step carries no key
     * to match an incoming one against — and replacing them discards the completion the person
     * doing the work recorded. Omit the key to leave them alone. `managers` likewise states
     * who approves now rather than adding to them.
     *
     * `kpi_plan` has no "unset" — the column is NOT NULL with a default of 0, so an omitted
     * plan is a plan of zero.
     *
     * @param TasksUpsertBody $body
     *
     * @return TasksUpsertResponse
     *
     * @throws ApiException|InvalidBodyException|TransportException
     */
    public function upsert(array $body): array
    {
        /** @var TasksUpsertResponse $answer */
        $answer = $this->caller->call(
            'POST',
            '/company/v3/tasks/upsert',
            body: $body,
        );

        return $answer;
    }
}
