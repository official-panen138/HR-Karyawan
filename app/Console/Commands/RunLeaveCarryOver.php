<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\EmployeeLeaveBalance;
use App\Models\LeaveType;
use Illuminate\Console\Command;

class RunLeaveCarryOver extends Command
{
    protected $signature = 'hr:leave-carry-over {--year= : Target year (defaults to current year)}';
    protected $description = 'Carry over remaining leave days from previous year and create new year balances';

    public function handle(): int
    {
        $targetYear = (int) ($this->option('year') ?? now()->year);
        $previousYear = $targetYear - 1;

        $leaveTypes = LeaveType::where('is_active', true)->get();
        $employees = Employee::active()->get();

        $created = 0;
        $carried = 0;

        foreach ($employees as $employee) {
            foreach ($leaveTypes as $type) {
                if (!$type->default_quota_days) continue;

                // Check if balance already exists for target year
                $exists = EmployeeLeaveBalance::where('employee_id', $employee->id)
                    ->where('leave_type_id', $type->id)
                    ->where('year', $targetYear)
                    ->exists();

                if ($exists) continue;

                $carryOverDays = 0;

                if ($type->allow_carry_over) {
                    $previousBalance = EmployeeLeaveBalance::where('employee_id', $employee->id)
                        ->where('leave_type_id', $type->id)
                        ->where('year', $previousYear)
                        ->first();

                    if ($previousBalance) {
                        $remaining = $previousBalance->remaining_days;
                        $carryOverDays = min($remaining, $type->max_carry_over_days);
                        $carryOverDays = max(0, $carryOverDays);
                        if ($carryOverDays > 0) $carried++;
                    }
                }

                EmployeeLeaveBalance::create([
                    'employee_id' => $employee->id,
                    'leave_type_id' => $type->id,
                    'year' => $targetYear,
                    'quota_days' => $type->default_quota_days,
                    'carry_over_days' => $carryOverDays,
                ]);

                $created++;
            }
        }

        $this->info("Created {$created} leave balances for {$targetYear}. Carried over {$carried} balances.");
        return self::SUCCESS;
    }
}
