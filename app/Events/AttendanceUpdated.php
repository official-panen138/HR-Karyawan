<?php

namespace App\Events;

use App\Models\Attendance;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AttendanceUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Attendance $attendance) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("employee.{$this->attendance->employee_id}"),
            new PrivateChannel("division.{$this->attendance->employee->division_id}"),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->attendance->id,
            'employee_id' => $this->attendance->employee_id,
            'status' => $this->attendance->status,
            'check_in_at' => $this->attendance->check_in_at?->toISOString(),
            'check_out_at' => $this->attendance->check_out_at?->toISOString(),
        ];
    }
}
