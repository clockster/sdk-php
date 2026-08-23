<?php

declare(strict_types=1);

namespace Clockster\Generated;

use Clockster\Exception\ApiException;
use Clockster\Exception\InvalidBodyException;
use Clockster\Exception\TransportException;
use Clockster\Http\Caller;

/**
 * The operations of `$clockster->schedules`.
 *
 * @phpstan-import-type SchedulesCreateBody from Shapes
 * @phpstan-import-type SchedulesCreateResponse from Shapes
 * @phpstan-import-type SchedulesDeleteResponse from Shapes
 * @phpstan-import-type SchedulesGetResponse from Shapes
 */
final class Schedules
{
    public function __construct(private readonly Caller $caller)
    {
    }

    /**
     * Create schedules.
     *
     * Up to 25 schedules in one call, each a kind of day, the days it falls on, and the people
     * it is for. `type` is per item, so a rota and the absences inside it go together.
     *
     * There are no repeat patterns: send the dates. If you want every Monday, say which
     * Mondays.
     *
     * **Send the rota as one call, not as twenty.** Everyone named is notified, once per call —
     * so the same twenty schedules sent one at a time buzz in somebody's pocket twenty times.
     *
     * **This is the write to send an `Idempotency-Key` with.** A schedule carries no key of
     * yours, so a retry after a timeout files the rota a second time unless the header tells us
     * it is the same attempt.
     *
     * **Schedules carry no key you own**, so a resend duplicates rather than converges. A
     * `422` means none of the batch landed and is safe to fix and send again; a timeout is
     * not — read back before retrying. The answer lists what was created, in the order sent.
     *
     * **`type` decides what else is required.** `work` needs `timezone` and either
     * `start`/`end` or `shifts`. `free` — a day with hours to make up rather than hours to
     * keep — needs `timezone`, `start`, `end`, and takes `time_planned`. `leave` needs only
     * `leave_type`.
     *
     * `start` and `end` are clock times, `HH:MM:SS`, read in `timezone` — not instants, whatever
     * a generated client calls the field. `timezone` is a fixed offset, `+05:00` or `Z`.
     *
     * **The answer is not an echo of the request, so read it.** A day with two or more `shifts`
     * takes its `start`, `end` and `time_planned` from them and comes back with `is_split`
     * true. A day with exactly one shift is not a split day: the hours move onto the day itself
     * and `shifts` comes back empty. `time_planned` for a worked day is always computed —
     * the hours less the break — never taken from what you sent.
     *
     * **Every span is seconds**, `break_time` and `grace_start`/`grace_end` included. Grace is
     * stored to the minute, so send a multiple of 60; the maximum is 3600.
     *
     * **Grace is not the same as a boundary.** It is how far past the start a person may arrive
     * and still be credited from the shift boundary. How far outside the shift a punch is
     * collected at all is a company setting and is not on this endpoint.
     *
     * There is no `title` — one is generated and it means nothing to you. One schedule takes up
     * to 366 dates, 200 people and 8 shifts.
     *
     * @param SchedulesCreateBody $body
     * @param string|null $idempotencyKey A value of your own, so a retry of this write is answered with the first result rather than performed again.
     *
     * @return SchedulesCreateResponse
     *
     * @throws ApiException|InvalidBodyException|TransportException
     */
    public function create(array $body, ?string $idempotencyKey = null): array
    {
        /** @var SchedulesCreateResponse $answer */
        $answer = $this->caller->call(
            'POST',
            '/company/v3/schedules',
            body: $body,
            idempotencyKey: $idempotencyKey,
        );

        return $answer;
    }

    /**
     * Delete a schedule.
     *
     * Everyone who was on it is notified, the same way a change to it would notify them.
     *
     * **A default schedule is refused** — 409, `schedule_is_default`. It is what the company
     * falls back to.
     *
     * **An open shift is refused** — 409, `schedule_is_open`. Deleting one also removes its
     * siblings and recomputes their days, which this API can neither create nor show you. Use
     * the web application.
     *
     * @param int $id The id of the row, as this API issued it.
     *
     * @return SchedulesDeleteResponse
     *
     * @throws ApiException|TransportException
     */
    public function delete(int $id): array
    {
        /** @var SchedulesDeleteResponse $answer */
        $answer = $this->caller->call(
            'DELETE',
            sprintf('/company/v3/schedules/%d', $id),
        );

        return $answer;
    }

    /**
     * Read one schedule.
     *
     * Exactly what creating it answered with — the same keys, the shifts and the people
     * included, since those are the schedule rather than an optional extra.
     *
     * Worth reading back after a create: a day with two or more shifts takes its `start`,
     * `end` and `time_planned` from them, and a day with exactly one has the shift folded into
     * it and comes back with `shifts` empty.
     *
     * There is no listing of schedules. `GET /company/v3/timesheets` answers what a person is
     * scheduled for on a day, which is the question a rota is usually asked.
     *
     * @param int $id The id of the row, as this API issued it.
     *
     * @return SchedulesGetResponse
     *
     * @throws ApiException|TransportException
     */
    public function get(int $id): array
    {
        /** @var SchedulesGetResponse $answer */
        $answer = $this->caller->call(
            'GET',
            sprintf('/company/v3/schedules/%d', $id),
        );

        return $answer;
    }
}
