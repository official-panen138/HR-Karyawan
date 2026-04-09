<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveTypeConfig extends Model
{
    use HasUuid;

    protected $fillable = [
        'division_id',
        'leave_type_id',
        'quota_days',
        'max_concurrent',
        'approval_levels',
        'blackout_dates',
    ];

    protected function casts(): array
    {
        return [
            'quota_days' => 'integer',
            'max_concurrent' => 'integer',
            'approval_levels' => 'integer',
            'blackout_dates' => 'array',
        ];
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }
}
