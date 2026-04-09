<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'start_date' => $this->start_date->format('Y-m-d'),
            'end_date' => $this->end_date->format('Y-m-d'),
            'total_days' => $this->total_days,
            'reason' => $this->reason,
            'status' => $this->status,
            'is_emergency' => $this->is_emergency,
            'contact_during_leave' => $this->contact_during_leave,
            'handover_notes' => $this->handover_notes,
            'submitted_at' => $this->submitted_at?->toISOString(),
            'employee' => new EmployeeResource($this->whenLoaded('employee')),
            'leave_type' => $this->whenLoaded('leaveType'),
            'approvals' => LeaveApprovalResource::collection($this->whenLoaded('approvals')),
            'created_at' => $this->created_at,
        ];
    }
}
