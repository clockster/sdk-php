<?php

declare(strict_types=1);

namespace Clockster\Generated\Enum;

/**
 * What `type` is allowed to be.
 *
 * Sent in a `$clockster->payroll->singleAdjustments->create()` body, a filter on
 * `$clockster->payroll->singleAdjustments->list()`.
 *
 * Constants rather than the cases of an enum, so one goes wherever the string goes:
 * `PayrollSingleAdjustmentsType::SERVICE_CHARGE` is `'service_charge'`, and a static analyser reads
 * the two as one value. Closed on the way in and only there — an answer naming something this
 * class does not is still a string, and still reaches you.
 */
final class PayrollSingleAdjustmentsType
{
    public const SERVICE_CHARGE = 'service_charge';
    public const SINGLE_ADDITION_PRE_TAX = 'single_addition_pre_tax';
    public const SINGLE_ADDITION_POST_TAX = 'single_addition_post_tax';
    public const SINGLE_LOAN = 'single_loan';
    public const SINGLE_DEDUCTION_PRE_TAX = 'single_deduction_pre_tax';
    public const SINGLE_DEDUCTION_POST_TAX = 'single_deduction_post_tax';

    /** @return list<'service_charge'|'single_addition_pre_tax'|'single_addition_post_tax'|'single_loan'|'single_deduction_pre_tax'|'single_deduction_post_tax'> */
    public static function values(): array
    {
        return [
            self::SERVICE_CHARGE,
            self::SINGLE_ADDITION_PRE_TAX,
            self::SINGLE_ADDITION_POST_TAX,
            self::SINGLE_LOAN,
            self::SINGLE_DEDUCTION_PRE_TAX,
            self::SINGLE_DEDUCTION_POST_TAX,
        ];
    }
}
