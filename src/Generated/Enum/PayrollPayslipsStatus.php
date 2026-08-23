<?php

declare(strict_types=1);

namespace Clockster\Generated\Enum;

/**
 * What `status` is allowed to be.
 *
 * Sent in a filter on $clockster->payroll->payslips->list().
 *
 * Constants rather than the cases of an enum, so one goes wherever the string goes:
 * `PayrollPayslipsStatus::DRAFT` is `'draft'`, and a static analyser reads the two as one value.
 * Closed on the way in and only there — an answer naming something this class does not is still a
 * string, and still reaches you.
 */
final class PayrollPayslipsStatus
{
    public const DRAFT = 'draft';
    public const APPROVED = 'approved';
    public const PAID = 'paid';

    /** @return list<'draft'|'approved'|'paid'> */
    public static function values(): array
    {
        return [
            self::DRAFT,
            self::APPROVED,
            self::PAID,
        ];
    }
}
