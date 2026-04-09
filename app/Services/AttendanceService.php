<?php

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Enums\BreakCategory;
use App\Models\Attendance;
use App\Models\BreakLog;
use App\Models\BreakQuota;
use App\Models\Employee;
use App\Models\EmployeeShift;
use Illuminate\Support\Carbon;

class AttendanceService
{
    public function checkIn(Employee $employee, string $ip): Attendance
    {
        $today = now()->toDateString();

        $existing = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();

        if ($existing && $existing->check_in_at) {
            throw new \RuntimeException('Kamu sudah check-in hari ini');
        }

        $shift = $this->getCurrentShift($employee);
        $lateMinutes = 0;

        if ($shift) {
            $shiftStart = Carbon::parse($today . ' ' . $shift->start_time);
            if (now()->gt($shiftStart)) {
                $lateMinutes = (int) now()->diffInMinutes($shiftStart);
            }
        }

        $status = $lateMinutes > 0 ? AttendanceStatus::LATE : AttendanceStatus::PRESENT;

        if ($existing) {
            $existing->update([
                'check_in_at' => now(),
                'check_in_ip' => $ip,
                'shift_id' => $shift?->id,
                'late_minutes' => $lateMinutes,
                'status' => $status,
            ]);
            return $existing->fresh(['employee', 'shift', 'breakLogs']);
        }

        return Attendance::create([
            'employee_id' => $employee->id,
            'shift_id' => $shift?->id,
            'date' => $today,
            'check_in_at' => now(),
            'check_in_ip' => $ip,
            'late_minutes' => $lateMinutes,
            'status' => $status,
        ]);
    }

    public function checkOut(Employee $employee, string $ip): Attendance
    {
        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('date', now()->toDateString())
            ->firstOrFail();

        if (!$attendance->check_in_at) {
            throw new \RuntimeException('Belum check-in hari ini');
        }

        if ($attendance->check_out_at) {
            throw new \RuntimeException('Sudah check-out hari ini');
        }

        // Close active break
        $activeBreak = $attendance->breakLogs()->whereNull('ended_at')->first();
        if ($activeBreak) {
            $this->endBreak($activeBreak);
        }

        $effectiveMinutes = (int) Carbon::parse($attendance->check_in_at)
            ->diffInMinutes(now());

        // Subtract break time
        $totalBreakMinutes = $attendance->breakLogs()->sum('duration_minutes');
        $effectiveMinutes -= $totalBreakMinutes;

        $attendance->update([
            'check_out_at' => now(),
            'check_out_ip' => $ip,
            'effective_minutes' => max(0, $effectiveMinutes),
        ]);

        return $attendance->fresh(['employee', 'shift', 'breakLogs']);
    }

    public function startBreak(Employee $employee, BreakCategory $category, string $ip): BreakLog
    {
        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('date', now()->toDateString())
            ->firstOrFail();

        if (!$attendance->check_in_at || $attendance->check_out_at) {
            throw new \RuntimeException('Tidak dalam sesi kerja');
        }

        $activeBreak = $attendance->breakLogs()->whereNull('ended_at')->first();
        if ($activeBreak) {
            throw new \RuntimeException('Masih ada break aktif');
        }

        // Check quota
        $quota = BreakQuota::where('category', $category)
            ->where(function ($q) use ($employee) {
                $q->whereNull('division_id')
                    ->orWhere('division_id', $employee->division_id);
            })
            ->where('is_active', true)
            ->orderByDesc('division_id')
            ->first();

        if ($quota) {
            $usedMinutes = $attendance->breakLogs()
                ->where('category', $category)
                ->sum('duration_minutes');

            if ($usedMinutes >= $quota->quota_minutes) {
                throw new \RuntimeException("Kuota break {$category->value} sudah habis");
            }

            $totalUsed = $attendance->breakLogs()->sum('duration_minutes');
            if ($totalUsed >= $quota->global_minutes_limit) {
                throw new \RuntimeException('Kuota break global sudah habis');
            }
        }

        return BreakLog::create([
            'attendance_id' => $attendance->id,
            'category' => $category,
            'started_at' => now(),
            'started_ip' => $ip,
        ]);
    }

    public function endBreak(BreakLog $breakLog): BreakLog
    {
        if ($breakLog->ended_at) {
            throw new \RuntimeException('Break sudah selesai');
        }

        $duration = (int) Carbon::parse($breakLog->started_at)->diffInMinutes(now());

        $breakLog->update([
            'ended_at' => now(),
            'duration_minutes' => max(1, $duration),
        ]);

        return $breakLog->fresh();
    }

    private function getCurrentShift(Employee $employee): ?\App\Models\Shift
    {
        $assignment = EmployeeShift::where('employee_id', $employee->id)
            ->where('start_date', '<=', now()->toDateString())
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', now()->toDateString());
            })
            ->with('shift')
            ->first();

        return $assignment?->shift;
    }
}
