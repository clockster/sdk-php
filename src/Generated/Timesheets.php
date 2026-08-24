<?php

declare(strict_types=1);

namespace Clockster\Generated;

use Clockster\Exception\ApiException;
use Clockster\Exception\TransportException;
use Clockster\Http\Caller;
use Generator;

/**
 * The operations of `$clockster->timesheets`.
 *
 * @phpstan-import-type TimesheetsInclude from Shapes
 * @phpstan-import-type TimesheetsListResponse from Shapes
 * @phpstan-import-type TimesheetsListRow from Shapes
 */
final class Timesheets
{
    public function __construct(private readonly Caller $caller)
    {
    }

    /**
     * Timesheets.
     *
     * One row per person per calendar day of the window: what was planned, and — when asked
     * for — what happened and how the two differ.
     *
     * **`planned` alone is the timesheet grid** — who was meant to work, when, and what kind
     * of day it was. `include=actual` adds what was recorded, `include=variance` adds the
     * difference. Either one is what makes the request expensive, because both require
     * matching the clock-ins.
     *
     * **The four variance numbers are not additive.** Arriving three minutes late produces
     * `time_late` 180 and `time_underworked` 180 — the same minutes, counted once as lateness
     * and once as unfilled plan. Summing them double-counts.
     *
     * **`planned: null` means no schedule at all for that day.** It is rarer than it sounds: a
     * company created with default settings carries a work and a leave schedule covering four
     * years, so ordinary days off arrive as `type: leave` rather than as an absent plan. A day
     * that was scheduled and not worked is the other case — a plan, an empty `actual`, and
     * `time_underworked` equal to the whole planned time.
     *
     * **There is no `per_page`.** How many rows fifty people produce depends on the window and
     * on who is scheduled, which the caller cannot predict and we can: the page is sized to a
     * row budget instead, and `meta.users_per_page` reports what that came to. Paging walks
     * people, so one person's whole period always arrives on a single page and a monthly total
     * never has to be assembled across two.
     *
     * Holidays are not marked: take them from your own calendar. Drafts are never returned.
     *
     * @param string $dateFrom Start of the window, inclusive (YYYY-MM-DD).
     * @param string $dateTo End of the window, inclusive (YYYY-MM-DD).
     * @param string|null $cursor The `meta.next_cursor` of the previous page. Omit it for the first. A cursor is bound to the filters it was issued under — change them and start again.
     * @param list<int> $users Only rows belonging to these people, by id.
     * @param list<int> $locations Only these locations, by id.
     * @param list<int> $departments Only these departments, by id.
     * @param list<int> $positions Only these positions, by id.
     * @param string|null $employment Only people on these employment terms.
     * @param list<TimesheetsInclude> $include Relations to load, comma-separated. Anything not
     * named is absent from the answer rather than null.
     *
     * @return TimesheetsListResponse
     *
     * @throws ApiException|TransportException
     */
    public function list(
        string $dateFrom,
        string $dateTo,
        ?string $cursor = null,
        array $users = [],
        array $locations = [],
        array $departments = [],
        array $positions = [],
        ?string $employment = null,
        array $include = [],
    ): array {
        /** @var TimesheetsListResponse $answer */
        $answer = $this->caller->call(
            'GET',
            '/company/v3/timesheets',
            [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'cursor' => $cursor,
                'users' => $users,
                'locations' => $locations,
                'departments' => $departments,
                'positions' => $positions,
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
     * @param string $dateFrom Start of the window, inclusive (YYYY-MM-DD).
     * @param string $dateTo End of the window, inclusive (YYYY-MM-DD).
     * @param list<int> $users Only rows belonging to these people, by id.
     * @param list<int> $locations Only these locations, by id.
     * @param list<int> $departments Only these departments, by id.
     * @param list<int> $positions Only these positions, by id.
     * @param string|null $employment Only people on these employment terms.
     * @param list<TimesheetsInclude> $include Relations to load, comma-separated. Anything not
     * named is absent from the answer rather than null.
     *
     * @return \Generator<int, TimesheetsListRow>
     *
     * @throws ApiException|TransportException
     */
    public function listAll(
        string $dateFrom,
        string $dateTo,
        array $users = [],
        array $locations = [],
        array $departments = [],
        array $positions = [],
        ?string $employment = null,
        array $include = [],
    ): Generator {
        $cursor = null;
        $seen = [];

        while (true) {
            $page = $this->list(
                dateFrom: $dateFrom,
                dateTo: $dateTo,
                users: $users,
                locations: $locations,
                departments: $departments,
                positions: $positions,
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
}
