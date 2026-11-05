<?php

declare(strict_types=1);

namespace Clockster\Generated;

use Clockster\Exception\ApiException;
use Clockster\Exception\InvalidBodyException;
use Clockster\Exception\TransportException;
use Clockster\Http\Caller;
use Generator;

/**
 * The operations of `$clockster->checklists`.
 *
 * @phpstan-import-type ChecklistsCreateBody from Shapes
 * @phpstan-import-type ChecklistsCreateResponse from Shapes
 * @phpstan-import-type ChecklistsDeleteResponse from Shapes
 * @phpstan-import-type ChecklistsGetResponse from Shapes
 * @phpstan-import-type ChecklistsInclude from Shapes
 * @phpstan-import-type ChecklistsListResponse from Shapes
 * @phpstan-import-type ChecklistsListRow from Shapes
 * @phpstan-import-type ChecklistsStatus from Shapes
 * @phpstan-import-type ChecklistsType from Shapes
 */
final class Checklists
{
    public function __construct(private readonly Caller $caller)
    {
    }

    /**
     * Create checklists.
     *
     * Up to 100 checklists in one call, each created once under your `external_id`, which is
     * required here.
     *
     * **One checklist is one row shared by everyone it is aimed at**, as the web app creates
     * it, and whoever fills it in answers for all of them. A result per location needs a
     * checklist per location.
     *
     * **Somebody has to be able to fill it in**: its author, its `users`, or anyone at its
     * `locations`, `departments` or `positions`. A new row that names none of them is a `422`.
     *
     * **Only today's checklists.** A new key's `date_start` is today in its `timezone`. The
     * lists in the product file a checklist by the UTC date it was created on, so send a day's
     * checklists while that date is also the date in UTC, or they show under another day.
     *
     * **The "new checklist" push** goes out at `time_notify`, else at `time_start`, today — at
     * once when neither is set, and not at all once that time has passed. It reaches the people
     * in a named audience, plus `users` and `author_id`.
     *
     * **Create only, so `exists` means nothing was applied.** A key already on file answers
     * `exists` with its id, whatever the row sent now says, so a resend after a timeout is
     * safe. A checklist nobody has touched yet is changed by deleting it and creating it again;
     * a missed one is rescheduled under a new `external_id`.
     *
     * A new key's ids are checked against your company, and the flags left out stay off. The
     * answer lists every row, in the order sent, as `created` or `exists`.
     *
     * What each field is:
     *
     * - `checklists` — The checklists to create, up to 100 a call and 2,000 items across them.
     * - `checklists[].external_id` — Your own key for this checklist. A key already on file
     *   answers `exists` and nothing is applied.
     * - `checklists[].title` — The name this is shown under.
     * - `checklists[].description` — Free text about this row, for people rather than for your
     *   code.
     * - `checklists[].type` — On a checklist, `default` or `audit`. On an item, the answer it
     *   takes: `checkbox` or `boolean` a boolean, `option` one of `bad`, `normal` or `good`,
     *   `number` an integer, `comment` free text.
     * - `checklists[].timezone` — The UTC offset the clock times beside it are read in — `Z`,
     *   or `+05:00`. An offset rather than a zone name, so the day is fixed to a moment rather than
     *   to a rule that may be changed later.
     * - `checklists[].date_start` — The day it opens, `YYYY-MM-DD`: today in `timezone` for a new
     *   key.
     * - `checklists[].date_end` — The last day it may be filled in, `YYYY-MM-DD`: on or after
     *   `date_start`, at most 30 days past it.
     * - `checklists[].time_start` — When in the day it starts, as a clock time `HH:MM:SS`.
     * - `checklists[].time_end` — When in the day it ends, as a clock time `HH:MM:SS`.
     * - `checklists[].time_notify` — When today the "new checklist" push goes out, `HH:MM:SS` in
     *   `timezone`.
     * - `checklists[].is_keep_status` — Keeps the status when the time runs out: an untouched
     *   checklist stays `created` rather than turning `pastdue`, and a started one stays `started`
     *   rather than being handed in by itself.
     * - `checklists[].min_score` — The pass mark for `score_percent`. A handed-in checklist below
     *   `min_score`, or with any item left unticked, is `incompleted`; an open required item
     *   refuses the hand-in. With `req_approve` it goes to `pending`, and the same rule settles it
     *   once approved.
     * - `checklists[].author_id` — Who it is filed under, by id; the author can fill it in too.
     *   Without it the checklist has no author, and the checklists report names the calling key
     *   instead.
     * - `checklists[].locations` — Aimed at people at these locations, by id. Left out or empty:
     *   every location. Your company's own locations only: one shared from a parent company, which
     *   `GET /company/v3/locations` also lists, is a `422`.
     * - `checklists[].departments` — Aimed at people in these departments, by id. Left out or
     *   empty: every department.
     * - `checklists[].positions` — Aimed at people in these positions, by id. Left out or empty:
     *   every position.
     * - `checklists[].user_filters` — Aimed at people in these user filters, by id. Left out or
     *   empty: every user filter. It narrows who sees the checklist but lets nobody fill it in.
     * - `checklists[].users` — People the checklist is assigned to by name, by id, on top of the
     *   audience.
     * - `checklists[].managers` — Who approves or is notified, by id. Required with `req_approve`
     *   or `req_notify`.
     * - `checklists[].items` — The questions, in the order given.
     * - `checklists[].items[].section` — The heading the item sits under. Items naming the same
     *   section share it; an item without one sits under an unnamed one.
     * - `checklists[].items[].title` — The name this is shown under.
     * - `checklists[].items[].type` — On a checklist, `default` or `audit`. On an item, the
     *   answer it takes: `checkbox` or `boolean` a boolean, `option` one of `bad`, `normal` or
     *   `good`, `number` an integer, `comment` free text.
     * - `checklists[].items[].time_start` — When in the day it starts, as a clock time
     *   `HH:MM:SS`.
     * - `checklists[].items[].time_end` — When in the day it ends, as a clock time `HH:MM:SS`.
     * - `checklists[].items[].score` — What the item weighs in `score_percent`.
     *
     * @param ChecklistsCreateBody $body
     *
     * @return ChecklistsCreateResponse
     *
     * @throws ApiException|InvalidBodyException|TransportException
     */
    public function create(array $body): array
    {
        /** @var ChecklistsCreateResponse $answer */
        $answer = $this->caller->call(
            'POST',
            '/company/v3/checklists',
            body: $body,
        );

        return $answer;
    }

    /**
     * Delete a checklist.
     *
     * Deletes a checklist nobody has touched, and its discussion.
     *
     * **Refused once anybody has worked on it** — 409, `checklist_locked`: it was started or
     * is past due, an item was answered or carries a file, or it has an issue or a comment.
     * Files on the checklist itself lock nothing, whoever uploaded them, and go with it. One
     * with `is_keep_status` never turns past due, so it stays deletable after its dates until
     * somebody works on it. The refusal is final; reschedule under a new `external_id` instead.
     *
     * A `404` on a repeated delete means it is gone. The id comes from the create answer or
     * from `GET /company/v3/checklists?external_ids=`. A deletion is not visible to polling
     * with `updated_since`.
     *
     * @param int $id The id of the row, as this API issued it.
     *
     * @return ChecklistsDeleteResponse
     *
     * @throws ApiException|TransportException
     */
    public function delete(int $id): array
    {
        /** @var ChecklistsDeleteResponse $answer */
        $answer = $this->caller->call(
            'DELETE',
            sprintf('/company/v3/checklists/%d', $id),
        );

        return $answer;
    }

    /**
     * Read one checklist.
     *
     * The same keys the listing answers with, and the same `include` vocabulary. Another company's
     * id is a `404`.
     *
     * @param int $id The id of the row, as this API issued it.
     * @param list<ChecklistsInclude> $include Relations to load, comma-separated. Anything not
     * named is absent from the answer rather than null.
     *
     * @return ChecklistsGetResponse
     *
     * @throws ApiException|TransportException
     */
    public function get(int $id, array $include = []): array
    {
        /** @var ChecklistsGetResponse $answer */
        $answer = $this->caller->call(
            'GET',
            sprintf('/company/v3/checklists/%d', $id),
            [
                'include' => $include,
            ],
        );

        return $answer;
    }

    /**
     * List checklists.
     *
     * Checklists as we hold them, oldest first, paged on a cursor: what was issued, how far it
     * got, and what was answered.
     *
     * **One checklist is one row shared by everyone it is aimed at.** Whoever fills it in
     * answers for all of them: its author, its `users`, or anyone at its `locations`,
     * `departments` or `positions`. `user_filters` narrows who sees it but lets nobody fill it
     * in. A result per location needs a checklist per location.
     *
     * `include=items` adds the questions with their answers. **An item's `value` follows its
     * `type`**: `checkbox` and `boolean` answer a boolean, `option` one of `bad`, `normal` or
     * `good`, `number` an integer, `comment` a string. It is null until somebody answers.
     *
     * `score_percent` is what the answered items scored, weighted by their `score`. A
     * handed-in checklist below `min_score`, or with any item left unticked, is `incompleted`;
     * an open required item refuses the hand-in. With `req_approve` it goes to `pending`, and
     * the same rule settles it once approved.
     *
     * `locations` matches a checklist aimed at one of those locations; one aimed at every
     * location (empty `locations`) is not matched. `start_from` and `start_to` are on
     * `date_start`, and a checklist without one is not matched.
     *
     * **`updated_since` sees the work**: an answer, a file added to or removed from the
     * checklist or one of its items, an approver's decision and an issue resolved from its task
     * all move `updated_at`. A comment does not, and neither does a template or a person
     * deleted for good, which empties `template_id`, `author_id` or an item's `user`, or drops
     * the person from `managers` and `users`. A deleted checklist is simply no longer listed,
     * so polling cannot see a deletion.
     *
     * @param int|null $perPage How many rows one page holds. Defaults to 50.
     * @param string|null $cursor The `meta.next_cursor` of the previous page. Omit it for the first. A cursor is bound to the filters it was issued under — change them and start again.
     * @param list<string> $externalIds Only rows carrying these keys of yours: write with your key,
     * read back with it.
     * @param list<ChecklistsStatus> $statuses Only rows in these states.
     * @param list<ChecklistsType> $types Only rows of these types.
     * @param list<int> $templates Only rows issued from these templates, by id.
     * @param list<int> $locations Only checklists aimed at one of these locations, by id. Your
     * company's own locations only: one shared from a parent company, which `GET
     * /company/v3/locations` also lists, is a `422`.
     * @param string|null $startFrom Starting on or after this date (YYYY-MM-DD).
     * @param string|null $startTo Starting on or before this date (YYYY-MM-DD).
     * @param string|null $updatedSince Only rows changed at or after this instant (ISO 8601). The cheap way to sync: ask for what moved, not for everything.
     * @param string|null $search Free text over the title.
     * @param list<ChecklistsInclude> $include Relations to load, comma-separated. Anything not
     * named is absent from the answer rather than null.
     *
     * @return ChecklistsListResponse
     *
     * @throws ApiException|TransportException
     */
    public function list(
        ?int $perPage = null,
        ?string $cursor = null,
        array $externalIds = [],
        array $statuses = [],
        array $types = [],
        array $templates = [],
        array $locations = [],
        ?string $startFrom = null,
        ?string $startTo = null,
        ?string $updatedSince = null,
        ?string $search = null,
        array $include = [],
    ): array {
        /** @var ChecklistsListResponse $answer */
        $answer = $this->caller->call(
            'GET',
            '/company/v3/checklists',
            [
                'per_page' => $perPage,
                'cursor' => $cursor,
                'external_ids' => $externalIds,
                'statuses' => $statuses,
                'types' => $types,
                'templates' => $templates,
                'locations' => $locations,
                'start_from' => $startFrom,
                'start_to' => $startTo,
                'updated_since' => $updatedSince,
                'search' => $search,
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
     * @param list<string> $externalIds Only rows carrying these keys of yours: write with your key,
     * read back with it.
     * @param list<ChecklistsStatus> $statuses Only rows in these states.
     * @param list<ChecklistsType> $types Only rows of these types.
     * @param list<int> $templates Only rows issued from these templates, by id.
     * @param list<int> $locations Only checklists aimed at one of these locations, by id. Your
     * company's own locations only: one shared from a parent company, which `GET
     * /company/v3/locations` also lists, is a `422`.
     * @param string|null $startFrom Starting on or after this date (YYYY-MM-DD).
     * @param string|null $startTo Starting on or before this date (YYYY-MM-DD).
     * @param string|null $updatedSince Only rows changed at or after this instant (ISO 8601). The cheap way to sync: ask for what moved, not for everything.
     * @param string|null $search Free text over the title.
     * @param list<ChecklistsInclude> $include Relations to load, comma-separated. Anything not
     * named is absent from the answer rather than null.
     *
     * @return \Generator<int, ChecklistsListRow>
     *
     * @throws ApiException|TransportException
     */
    public function listAll(
        ?int $perPage = null,
        array $externalIds = [],
        array $statuses = [],
        array $types = [],
        array $templates = [],
        array $locations = [],
        ?string $startFrom = null,
        ?string $startTo = null,
        ?string $updatedSince = null,
        ?string $search = null,
        array $include = [],
    ): Generator {
        $cursor = null;
        $seen = [];

        while (true) {
            $page = $this->list(
                perPage: $perPage,
                externalIds: $externalIds,
                statuses: $statuses,
                types: $types,
                templates: $templates,
                locations: $locations,
                startFrom: $startFrom,
                startTo: $startTo,
                updatedSince: $updatedSince,
                search: $search,
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
