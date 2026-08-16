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

    public function __construct(private readonly Caller $caller)
    {
        $this->payslips = new PayrollPayslips($caller);
    }
}
