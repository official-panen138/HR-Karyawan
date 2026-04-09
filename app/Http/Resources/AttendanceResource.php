<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'date' => $this->date->format('Y-m-d'),
            'check_in_at' => $this->check_in_at?->toISOString(),
            'check_out_at' => $this->check_out_at?->toISOString(),
            'check_in_ip' => $this->check_in_ip,
            'check_out_ip' => $this->check_out_ip,
            'late_minutes' => $this->late_minutes,
            'effective_minutes' => $this->effective_minutes,
            'status' => $this->status,
            'notes' => $this->notes,
            'employee' => new EmployeeResource($this->whenLoaded('employee')),
            'shift' => $this->whenLoaded('shift'),
            'break_logs' => BreakLogResource::collection($this->whenLoaded('breakLogs')),
        ];
    }
}
