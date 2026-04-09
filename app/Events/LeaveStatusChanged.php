<?php

namespace App\Events;

use App\Models\LeaveRequest;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LeaveStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public LeaveRequest $leaveRequest) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("employee.{$this->leaveRequest->employee_id}"),
            new PrivateChannel('hr'),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->leaveRequest->id,
            'status' => $this->leaveRequest->status,
            'employee_name' => $this->leaveRequest->employee->full_name,
            'total_days' => $this->leaveRequest->total_days,
        ];
    }
}
