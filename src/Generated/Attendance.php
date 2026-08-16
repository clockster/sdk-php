<?php

declare(strict_types=1);

namespace Clockster\Generated;

use Clockster\Exception\ApiException;
use Clockster\Exception\TransportException;
use Clockster\Http\Caller;
use Generator;

/**
 * The operations of `$clockster->attendance`.
 *
 * @phpstan-import-type AttendanceListResponse from Shapes
 * @phpstan-import-type AttendanceListRow from Shapes
 * @phpstan-import-type AttendanceRecordBody from Shapes
 * @phpstan-import-type AttendanceRecordResponse from Shapes
 */
final class Attendance
{
    public function __construct(private readonly Caller $caller)
    {
    }

    /**
     * List clock-ins.
     *
     * Marks inside a window of days, oldest first.
     *
     * **The window is required and capped at 90 days.** This is the largest table in the
     * product; an unbounded read of it has no plan that finishes. `date_from` and `date_to`
     * are plain dates matched against the clock the mark was stamped with, not against an
     * instant — a day means the same thing here as it does to the person who worked it.
     *
     * **`datetime` is the moment as it was recorded**, offset included — the stored wall clock
     * read in the stored zone, never shifted anywhere else. It is one field because it is one
     * fact: the wall clock is the value without its offset, and the zone is the offset. On the
     * rare row whose zone cannot be read it comes back with no offset at all, which is how you
     * see that the zone was not known.
     *
     * `status` is `in`, `out` or `break`, not the integer the column keeps.
     *
     * Late arrivals are the one thing to plan for: a device that was offline uploads what it
     * recorded earlier, so a mark can appear inside a window you have already read. Re-read
     * the last few days with overlap rather than paging strictly forward and never looking
     * back.
     *
     * @param string $dateFrom Start of the window, inclusive (YYYY-MM-DD).
     * @param string $dateTo End of the window, inclusive (YYYY-MM-DD).
     * @param int|null $perPage How many rows one page holds. Defaults to 50.
     * @param string|null $cursor The `meta.next_cursor` of the previous page. Omit it for the first. A cursor is bound to the filters it was issued under — change them and start again.
     * @param list<int> $users Only rows belonging to these people, by id.
     * @param list<int> $locations Only these locations, by id.
     * @param list<string> $statuses Only rows in these states.
     * @param list<string> $sources Only marks recorded this way — a device, the mobile app, or a
     * person entering them by hand.
     * @param list<string> $include Relations to load, comma-separated. Anything not named is absent
     * from the answer rather than null.
     *
     * @return AttendanceListResponse
     *
     * @throws ApiException|TransportException
     */
    public function list(
        string $dateFrom,
        string $dateTo,
        ?int $perPage = null,
        ?string $cursor = null,
        array $users = [],
        array $locations = [],
        array $statuses = [],
        array $sources = [],
        array $include = [],
    ): array {
        /** @var AttendanceListResponse $answer */
        $answer = $this->caller->call(
            'GET',
            '/company/v3/attendance',
            [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'per_page' => $perPage,
                'cursor' => $cursor,
                'users' => $users,
                'locations' => $locations,
                'statuses' => $statuses,
                'sources' => $sources,
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
     * @param int|null $perPage How many rows one page holds. Defaults to 50.
     * @param list<int> $users Only rows belonging to these people, by id.
     * @param list<int> $locations Only these locations, by id.
     * @param list<string> $statuses Only rows in these states.
     * @param list<string> $sources Only marks recorded this way — a device, the mobile app, or a
     * person entering them by hand.
     * @param list<string> $include Relations to load, comma-separated. Anything not named is absent
     * from the answer rather than null.
     *
     * @return \Generator<int, AttendanceListRow>
     *
     * @throws ApiException|TransportException
     */
    public function listAll(
        string $dateFrom,
        string $dateTo,
        ?int $perPage = null,
        array $users = [],
        array $locations = [],
        array $statuses = [],
        array $sources = [],
        array $include = [],
    ): Generator {
        $cursor = null;
        $seen = [];

        while (true) {
            $page = $this->list(
                dateFrom: $dateFrom,
                dateTo: $dateTo,
                perPage: $perPage,
                users: $users,
                locations: $locations,
                statuses: $statuses,
                sources: $sources,
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
     * Record attendance.
     *
     * Marks recorded by something you run — a turnstile of your own, a kiosk, your app.
     *
     * **`datetime` carries its own offset and is the only place time is stated** — send
     * `2026-08-10T09:03:00+05:00` and `09:03:00` goes on file with `+05:00` beside it. There
     * is no separate timezone field, and the offset is not optional: the wall clock and the
     * zone are read off that one value, so they cannot disagree. A mark may not be dated in the
     * future, nor more than 24 hours back: older than that is history being rewritten, and
     * lateness already computed against the day would move under it.
     *
     * `status` is `in`, `out` or `break`, and the answer echoes it back in those words, with
     * the moment as the instant it was recorded at — the same shapes the listing answers with.
     *
     * **A mark already on file for the same person, moment and direction is not written
     * again**, whether it repeats across two requests or inside one. There is no key you own
     * on this resource, so no promise of idempotency is made in general — but a retried upload
     * will not double somebody's day. The answer says `created` or `unchanged` per item, with
     * the id either way.
     *
     * The shift a mark belongs to is worked out afterwards, so `shift_id` is yours to send
     * only if you already know it.
     *
     * @param AttendanceRecordBody $body
     *
     * @return AttendanceRecordResponse
     *
     * @throws ApiException|TransportException
     */
    public function record(array $body): array
    {
        /** @var AttendanceRecordResponse $answer */
        $answer = $this->caller->call(
            'POST',
            '/company/v3/attendance',
            body: $body,
        );

        return $answer;
    }
}
