<?php

namespace App\Http\Controllers\Api;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\LeaveRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends BaseController
{
    public function dashboard(): JsonResponse
    {
        $today = now()->toDateString();

        return $this->success([
            'total_employees' => Employee::active()->count(),
            'present_today' => Attendance::where('date', $today)
                ->whereNotNull('check_in_at')
                ->count(),
            'pending_leaves' => LeaveRequest::where('status', 'pending')->count(),
            'expiring_documents' => EmployeeDocument::where('expired_at', '<=', now()->addDays(90))
                ->where('expired_at', '>=', now())
                ->count(),
            'expired_documents' => EmployeeDocument::where('expired_at', '<', now())->count(),
        ]);
    }

    public function attendance(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
        ]);

        $data = Attendance::with(['employee.division', 'employee.position'])
            ->whereBetween('date', [$request->start_date, $request->end_date])
            ->when($request->division_id, fn ($q) => $q->whereHas('employee', fn ($q2) => $q2->where('division_id', $request->division_id)))
            ->selectRaw("
                employee_id,
                COUNT(*) as total_days,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days,
                SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_days,
                SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_days,
                SUM(late_minutes) as total_late_minutes,
                AVG(effective_minutes) as avg_effective_minutes
            ")
            ->groupBy('employee_id')
            ->get()
            ->map(fn ($row) => [
                ...$row->toArray(),
                'employee' => $row->employee?->load(['division', 'position']),
            ]);

        return $this->success($data);
    }

    public function leave(Request $request): JsonResponse
    {
        $request->validate(['year' => ['sometimes', 'integer']]);

        $year = $request->year ?? now()->year;

        $data = LeaveRequest::with(['employee.division', 'leaveType'])
            ->whereYear('start_date', $year)
            ->when($request->division_id, fn ($q) => $q->whereHas('employee', fn ($q2) => $q2->where('division_id', $request->division_id)))
            ->selectRaw("
                leave_type_id,
                status,
                COUNT(*) as count,
                SUM(total_days) as total_days
            ")
            ->groupBy('leave_type_id', 'status')
            ->get();

        return $this->success($data);
    }

    public function documents(): JsonResponse
    {
        $documents = EmployeeDocument::with(['employee.division'])
            ->whereNotNull('expired_at')
            ->orderBy('expired_at')
            ->get()
            ->groupBy(function ($doc) {
                return $doc->status;
            });

        return $this->success($documents);
    }
}
