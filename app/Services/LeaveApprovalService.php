<?php

namespace App\Services;

use App\Enums\ApprovalStatus;
use App\Enums\LeaveRequestStatus;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveTypeConfig;
use App\Models\PublicHoliday;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class LeaveApprovalService
{
    public function submit(Employee $employee, array $data, ?object $file = null): LeaveRequest
    {
        return DB::transaction(function () use ($employee, $data, $file) {
            $totalDays = $this->calculateWorkingDays($data['start_date'], $data['end_date']);

            $leaveRequest = LeaveRequest::create([
                'employee_id' => $employee->id,
                'leave_type_id' => $data['leave_type_id'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'total_days' => $totalDays,
                'reason' => $data['reason'] ?? null,
                'is_emergency' => $data['is_emergency'] ?? false,
                'contact_during_leave' => $data['contact_during_leave'] ?? null,
                'handover_notes' => $data['handover_notes'] ?? null,
                'status' => LeaveRequestStatus::PENDING,
                'submitted_at' => now(),
            ]);

            if ($file) {
                $path = $file->store("leave-docs/{$employee->id}", 'r2');
                $leaveRequest->update(['r2_key' => $path]);
            }

            // Create approval chain
            $config = LeaveTypeConfig::where('division_id', $employee->division_id)
                ->where('leave_type_id', $data['leave_type_id'])
                ->first();

            $levels = $config?->approval_levels ?? 2;
            $this->createApprovalChain($leaveRequest, $employee, $levels);

            // Update pending days on balance
            $balance = $employee->leaveBalances()
                ->where('leave_type_id', $data['leave_type_id'])
                ->where('year', now()->year)
                ->first();

            if ($balance) {
                $balance->increment('pending_days', $totalDays);
            }

            return $leaveRequest->load(['employee', 'leaveType', 'approvals.approver']);
        });
    }

    public function approve(string $approvalId, string $approverId, ?string $notes = null): LeaveRequest
    {
        return DB::transaction(function () use ($approvalId, $approverId, $notes) {
            $approval = \App\Models\LeaveApproval::where('id', $approvalId)
                ->where('approver_id', $approverId)
                ->where('status', ApprovalStatus::PENDING)
                ->firstOrFail();

            $approval->update([
                'status' => ApprovalStatus::APPROVED,
                'notes' => $notes,
                'acted_at' => now(),
            ]);

            $leaveRequest = $approval->leaveRequest;

            // Check if all levels approved
            $allApproved = $leaveRequest->approvals()
                ->where('status', '!=', ApprovalStatus::APPROVED)
                ->doesntExist();

            if ($allApproved) {
                $leaveRequest->update(['status' => LeaveRequestStatus::APPROVED]);

                // Move from pending to used
                $balance = $leaveRequest->employee->leaveBalances()
                    ->where('leave_type_id', $leaveRequest->leave_type_id)
                    ->where('year', now()->year)
                    ->first();

                if ($balance) {
                    $balance->decrement('pending_days', $leaveRequest->total_days);
                    $balance->increment('used_days', $leaveRequest->total_days);
                }
            } else {
                $leaveRequest->update(['status' => LeaveRequestStatus::PARTIALLY_APPROVED]);
            }

            return $leaveRequest->fresh(['employee', 'leaveType', 'approvals.approver']);
        });
    }

    public function reject(string $approvalId, string $approverId, ?string $notes = null): LeaveRequest
    {
        return DB::transaction(function () use ($approvalId, $approverId, $notes) {
            $approval = \App\Models\LeaveApproval::where('id', $approvalId)
                ->where('approver_id', $approverId)
                ->where('status', ApprovalStatus::PENDING)
                ->firstOrFail();

            $approval->update([
                'status' => ApprovalStatus::REJECTED,
                'notes' => $notes,
                'acted_at' => now(),
            ]);

            $leaveRequest = $approval->leaveRequest;
            $leaveRequest->update(['status' => LeaveRequestStatus::REJECTED]);

            // Release pending days
            $balance = $leaveRequest->employee->leaveBalances()
                ->where('leave_type_id', $leaveRequest->leave_type_id)
                ->where('year', now()->year)
                ->first();

            if ($balance) {
                $balance->decrement('pending_days', $leaveRequest->total_days);
            }

            return $leaveRequest->fresh(['employee', 'leaveType', 'approvals.approver']);
        });
    }

    public function calculateWorkingDays(string $startDate, string $endDate): int
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $holidays = PublicHoliday::whereBetween('date', [$start, $end])->pluck('date')->toArray();

        $days = 0;
        $current = $start->copy();
        while ($current->lte($end)) {
            if (!$current->isWeekend() && !in_array($current->toDateString(), $holidays)) {
                $days++;
            }
            $current->addDay();
        }

        return max(1, $days);
    }

    private function createApprovalChain(LeaveRequest $request, Employee $employee, int $levels): void
    {
        // Simple chain: find users with progressively higher roles
        $approverRoles = ['supervisor', 'div_manager', 'hr_admin'];
        $level = 1;

        foreach ($approverRoles as $role) {
            if ($level > $levels) break;

            $approvers = \App\Models\User::role($role)->get();
            foreach ($approvers as $approver) {
                if ($approver->id === $employee->user?->id) continue;

                $request->approvals()->create([
                    'approver_id' => $approver->id,
                    'level' => $level,
                    'status' => ApprovalStatus::PENDING,
                ]);
                $level++;
                break;
            }
        }
    }
}
