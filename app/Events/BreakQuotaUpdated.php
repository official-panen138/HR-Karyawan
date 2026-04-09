<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BreakQuotaUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $employeeId,
        public string $category,
        public int $remainingMinutes,
        public int $totalUsed,
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel("employee.{$this->employeeId}")];
    }
}
