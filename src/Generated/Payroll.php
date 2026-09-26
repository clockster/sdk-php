<?php

declare(strict_types=1);

namespace Clockster\Generated;

use Clockster\Http\Caller;

/**
 * The operations of `$clockster->payroll`.
 */
final class Payroll
{
    /** The operations of `$clockster->payroll->payslips`. */
    public readonly PayrollPayslips $payslips;

    /** The operations of `$clockster->payroll->singleAdjustments`. */
    public readonly PayrollSingleAdjustments $singleAdjustments;

    public function __construct(private readonly Caller $caller)
    {
        $this->payslips = new PayrollPayslips($caller);
        $this->singleAdjustments = new PayrollSingleAdjustments($caller);
    }
}
