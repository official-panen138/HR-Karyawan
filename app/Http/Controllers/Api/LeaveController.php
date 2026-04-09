<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreLeaveRequest;
use App\Http\Resources\LeaveRequestResource;
use App\Models\EmployeeLeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Services\LeaveApprovalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaveController extends BaseController
{
    public function __construct(private LeaveApprovalService $service) {}

    public function index(Request $request): JsonResponse
    {
        $query = LeaveRequest::with(['employee.division', 'leaveType', 'approvals.approver'])
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->when($request->employee_id, fn ($q, $v) => $q->where('employee_id', $v))
            ->orderByDesc('created_at');

        // Staff can only see their own
        if ($request->user()->hasRole('staff')) {
            $query->where('employee_id', $request->user()->employee_id);
        }

        $leaves = $query->paginate($request->per_page ?? 15);

        return $this->paginated(
            $leaves->setCollection(
                $leaves->getCollection()->map(fn ($l) => new LeaveRequestResource($l))
            )
        );
    }

    public function store(StoreLeaveRequest $request): JsonResponse
    {
        $employee = $request->user()->employee;
        if (!$employee) {
            return $this->error('Akun tidak terhubung dengan data karyawan', 404);
        }

        try {
            $leaveRequest = $this->service->submit(
                $employee,
                $request->validated(),
                $request->file('file')
            );
            return $this->success(new LeaveRequestResource($leaveRequest), 'Pengajuan cuti berhasil', 201);
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    public function show(LeaveRequest $leaveRequest): JsonResponse
    {
        $leaveRequest->load(['employee.division', 'leaveType', 'approvals.approver']);
        return $this->success(new LeaveRequestResource($leaveRequest));
    }

    public function cancel(LeaveRequest $leaveRequest): JsonResponse
    {
        if (!in_array($leaveRequest->status->value, ['draft', 'pending'])) {
            return $this->error('Hanya bisa membatalkan pengajuan yang masih draft/pending', 422);
        }

        $leaveRequest->update(['status' => 'cancelled']);

        $balance = $leaveRequest->employee->leaveBalances()
            ->where('leave_type_id', $leaveRequest->leave_type_id)
            ->where('year', now()->year)
            ->first();

        if ($balance) {
            $balance->decrement('pending_days', $leaveRequest->total_days);
        }

        return $this->success(null, 'Pengajuan cuti dibatalkan');
    }

    public function types(): JsonResponse
    {
        $types = LeaveType::where('is_active', true)->get();
        return $this->success($types);
    }

    public function balances(Request $request): JsonResponse
    {
        $employee = $request->user()->employee;
        if (!$employee) {
            return $this->error('Akun tidak terhubung dengan data karyawan', 404);
        }

        $balances = EmployeeLeaveBalance::with('leaveType')
            ->where('employee_id', $employee->id)
            ->where('year', $request->year ?? now()->year)
            ->get()
            ->map(fn ($b) => [
                ...$b->toArray(),
                'remaining_days' => $b->remaining_days,
            ]);

        return $this->success($balances);
    }

    public function calculateDays(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $days = $this->service->calculateWorkingDays($request->start_date, $request->end_date);
        return $this->success(['total_days' => $days]);
    }
}
