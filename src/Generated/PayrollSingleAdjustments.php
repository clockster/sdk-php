<?php

declare(strict_types=1);

namespace Clockster\Generated;

use Clockster\Exception\ApiException;
use Clockster\Exception\InvalidBodyException;
use Clockster\Exception\TransportException;
use Clockster\Http\Caller;
use Generator;

/**
 * The operations of `$clockster->payroll->singleAdjustments`.
 *
 * @phpstan-import-type PayrollSingleAdjustmentsCreateBody from Shapes
 * @phpstan-import-type PayrollSingleAdjustmentsCreateResponse from Shapes
 * @phpstan-import-type PayrollSingleAdjustmentsDeleteResponse from Shapes
 * @phpstan-import-type PayrollSingleAdjustmentsListResponse from Shapes
 * @phpstan-import-type PayrollSingleAdjustmentsListRow from Shapes
 * @phpstan-import-type PayrollSingleAdjustmentsType from Shapes
 */
final class PayrollSingleAdjustments
{
    public function __construct(private readonly Caller $caller)
    {
    }

    /**
     * Create single adjustments.
     *
     * Up to 100 one-off amounts — a bonus, a service charge, a penalty — each for one person
     * and one day. All or nothing: a `422` means none of the batch landed.
     *
     * **An adjustment is read when a payslip is calculated.** A `draft` payslip whose period
     * holds `date` takes it in on its next calculation. An `approved` or `paid` one does not:
     * it is recalculated only by hand in the web application, and nothing here tells you
     * whether that happened. Check the payslip's status for the month before filing into it.
     *
     * **Send an `Idempotency-Key`.** An adjustment carries no key of yours, so a retry after a
     * timeout files it a second time unless the header says it is the same attempt. The
     * answer lists what was created, in the order sent.
     *
     * What each field is:
     *
     * - `adjustments` — The adjustments to file, up to 100 a call.
     * - `adjustments[].user_id` — The employee this belongs to, by the id this API issued.
     * - `adjustments[].type` — What the amount does: an addition or a deduction, before or after
     *   tax, a service charge or a one-off loan.
     * - `adjustments[].amount` — How much, never negative — `type` says which way it goes. At
     *   most two decimal places.
     * - `adjustments[].date` — The day it is dated, `YYYY-MM-DD`. The payslip whose period holds
     *   this day takes it in.
     * - `adjustments[].title` — The name this is shown under.
     *
     * @param PayrollSingleAdjustmentsCreateBody $body
     * @param string|null $idempotencyKey A value of your own, so a retry of this write is answered with the first result rather than performed again.
     *
     * @return PayrollSingleAdjustmentsCreateResponse
     *
     * @throws ApiException|InvalidBodyException|TransportException
     */
    public function create(array $body, ?string $idempotencyKey = null): array
    {
        /** @var PayrollSingleAdjustmentsCreateResponse $answer */
        $answer = $this->caller->call(
            'POST',
            '/company/v3/payroll/single-adjustments',
            body: $body,
            idempotencyKey: $idempotencyKey,
        );

        return $answer;
    }

    /**
     * Delete a single adjustment.
     *
     * Deletes the adjustment. A payslip already calculated with it keeps the amount until it
     * is calculated again — for an `approved` or `paid` one, only by hand in the web
     * application.
     *
     * Another company's id is a `404`.
     *
     * @param int $id The id of the row, as this API issued it.
     *
     * @return PayrollSingleAdjustmentsDeleteResponse
     *
     * @throws ApiException|TransportException
     */
    public function delete(int $id): array
    {
        /** @var PayrollSingleAdjustmentsDeleteResponse $answer */
        $answer = $this->caller->call(
            'DELETE',
            sprintf('/company/v3/payroll/single-adjustments/%d', $id),
        );

        return $answer;
    }

    /**
     * List single adjustments.
     *
     * One-off additions and deductions, oldest first, with who they are for and the day they
     * are dated.
     *
     * `amount` is never negative: `type` says whether it is added or taken off, and whether
     * before or after tax. `date_from` and `date_to` bound the day, inclusive.
     *
     * Rows filed in the web application are listed too, and may carry `13th_pay`, which is
     * computed there rather than filed here.
     *
     * @param int|null $perPage How many rows one page holds. Defaults to 50.
     * @param string|null $cursor The `meta.next_cursor` of the previous page. Omit it for the first. A cursor is bound to the filters it was issued under — change them and start again.
     * @param list<int> $users Only rows belonging to these people, by id.
     * @param list<PayrollSingleAdjustmentsType> $types Only rows of these types.
     * @param string|null $dateFrom Start of the window, inclusive (YYYY-MM-DD).
     * @param string|null $dateTo End of the window, inclusive (YYYY-MM-DD).
     *
     * @return PayrollSingleAdjustmentsListResponse
     *
     * @throws ApiException|TransportException
     */
    public function list(
        ?int $perPage = null,
        ?string $cursor = null,
        array $users = [],
        array $types = [],
        ?string $dateFrom = null,
        ?string $dateTo = null,
    ): array {
        /** @var PayrollSingleAdjustmentsListResponse $answer */
        $answer = $this->caller->call(
            'GET',
            '/company/v3/payroll/single-adjustments',
            [
                'per_page' => $perPage,
                'cursor' => $cursor,
                'users' => $users,
                'types' => $types,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
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
     * @param list<int> $users Only rows belonging to these people, by id.
     * @param list<PayrollSingleAdjustmentsType> $types Only rows of these types.
     * @param string|null $dateFrom Start of the window, inclusive (YYYY-MM-DD).
     * @param string|null $dateTo End of the window, inclusive (YYYY-MM-DD).
     *
     * @return \Generator<int, PayrollSingleAdjustmentsListRow>
     *
     * @throws ApiException|TransportException
     */
    public function listAll(
        ?int $perPage = null,
        array $users = [],
        array $types = [],
        ?string $dateFrom = null,
        ?string $dateTo = null,
    ): Generator {
        $cursor = null;
        $seen = [];

        while (true) {
            $page = $this->list(
                perPage: $perPage,
                users: $users,
                types: $types,
                dateFrom: $dateFrom,
                dateTo: $dateTo,
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
