<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\LeaveRequestResource;
use App\Models\LeaveApproval;
use App\Services\LeaveApprovalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaveApprovalController extends BaseController
{
    public function __construct(private LeaveApprovalService $service) {}

    public function pending(Request $request): JsonResponse
    {
        $approvals = LeaveApproval::with(['leaveRequest.employee.division', 'leaveRequest.leaveType'])
            ->where('approver_id', $request->user()->id)
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->get();

        return $this->success($approvals);
    }

    public function approve(Request $request, LeaveApproval $approval): JsonResponse
    {
        try {
            $leaveRequest = $this->service->approve(
                $approval->id,
                $request->user()->id,
                $request->notes
            );
            return $this->success(new LeaveRequestResource($leaveRequest), 'Cuti disetujui');
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    public function reject(Request $request, LeaveApproval $approval): JsonResponse
    {
        $request->validate(['notes' => ['required', 'string']]);

        try {
            $leaveRequest = $this->service->reject(
                $approval->id,
                $request->user()->id,
                $request->notes
            );
            return $this->success(new LeaveRequestResource($leaveRequest), 'Cuti ditolak');
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), 422);
        }
    }
}
