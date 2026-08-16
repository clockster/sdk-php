<?php

declare(strict_types=1);

namespace Clockster\Generated;

use Clockster\Exception\ApiException;
use Clockster\Exception\TransportException;
use Clockster\Http\Caller;
use Generator;

/**
 * The operations of `$clockster->payroll->payslips`.
 *
 * @phpstan-import-type PayrollPayslipsListResponse from Shapes
 * @phpstan-import-type PayrollPayslipsListRow from Shapes
 */
final class PayrollPayslips
{
    public function __construct(private readonly Caller $caller)
    {
    }

    /**
     * List payslips.
     *
     * Reading only. Nothing on this surface creates or changes a payslip.
     *
     * **Amounts carry their currency.** A payslip that carries none — one built from a salary
     * filed before the field was required — answers the company's own currency rather than
     * null, so you do not have to invent that fallback yourself.
     *
     * **The line items are here**: `additions`, `deductions` and `allowances`, each with its
     * `title`, `value` and `pre_tax`, which is what decides whether an amount lands before tax
     * is worked out.
     *
     * **`loan_repaid` is what was taken back against an advance.** An advance is not a
     * deduction: it becomes a loan and is repaid on scheduled days, so it never appears in
     * `deductions` and a caller subtracting the line items from the total will be out by
     * exactly this amount. The figure folds together loan repayments and one-off loan
     * adjustments, because that is how the calculation records them.
     *
     * **The parts do not add up to `take_home`, and are not meant to.** Taxes and the gross
     * figure are not published here, so what you get is what was added, taken off and repaid —
     * not a derivation of the total.
     *
     * **`updated_since` matters more here than anywhere**: a payslip is recalculated and moves
     * from `draft` to `approved` to `paid`, so without it a caller re-reads every month
     * forever. `months` is `YYYY-MM`, and takes a list, so a quarter is one request.
     *
     * @param int|null $perPage How many rows one page holds. Defaults to 50.
     * @param string|null $cursor The `meta.next_cursor` of the previous page. Omit it for the first. A cursor is bound to the filters it was issued under — change them and start again.
     * @param list<int> $users Only rows belonging to these people, by id.
     * @param list<string> $statuses Only rows in these states.
     * @param list<string> $months Only these months, as YYYY-MM.
     * @param string|null $updatedSince Only rows changed at or after this instant (ISO 8601). The cheap way to sync: ask for what moved, not for everything.
     *
     * @return PayrollPayslipsListResponse
     *
     * @throws ApiException|TransportException
     */
    public function list(
        ?int $perPage = null,
        ?string $cursor = null,
        array $users = [],
        array $statuses = [],
        array $months = [],
        ?string $updatedSince = null,
    ): array {
        /** @var PayrollPayslipsListResponse $answer */
        $answer = $this->caller->call(
            'GET',
            '/company/v3/payroll/payslips',
            [
                'per_page' => $perPage,
                'cursor' => $cursor,
                'users' => $users,
                'statuses' => $statuses,
                'months' => $months,
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
     * @param list<int> $users Only rows belonging to these people, by id.
     * @param list<string> $statuses Only rows in these states.
     * @param list<string> $months Only these months, as YYYY-MM.
     * @param string|null $updatedSince Only rows changed at or after this instant (ISO 8601). The cheap way to sync: ask for what moved, not for everything.
     *
     * @return \Generator<int, PayrollPayslipsListRow>
     *
     * @throws ApiException|TransportException
     */
    public function listAll(
        ?int $perPage = null,
        array $users = [],
        array $statuses = [],
        array $months = [],
        ?string $updatedSince = null,
    ): Generator {
        $cursor = null;
        $seen = [];

        while (true) {
            $page = $this->list(
                perPage: $perPage,
                users: $users,
                statuses: $statuses,
                months: $months,
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
}
