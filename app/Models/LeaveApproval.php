<?php

namespace App\Models;

use App\Enums\ApprovalStatus;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveApproval extends Model
{
    use HasUuid;

    protected $fillable = [
        'leave_request_id',
        'approver_id',
        'level',
        'status',
        'notes',
        'acted_at',
        'reminded_at',
        'telegram_message_id',
        'telegram_chat_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => ApprovalStatus::class,
            'level' => 'integer',
            'acted_at' => 'datetime',
            'reminded_at' => 'datetime',
            'telegram_message_id' => 'integer',
        ];
    }

    public function leaveRequest(): BelongsTo
    {
        return $this->belongsTo(LeaveRequest::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
