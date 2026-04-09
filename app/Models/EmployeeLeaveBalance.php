<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeLeaveBalance extends Model
{
    use HasUuid;

    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'year',
        'quota_days',
        'carry_over_days',
        'used_days',
        'pending_days',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'quota_days' => 'integer',
            'carry_over_days' => 'integer',
            'used_days' => 'integer',
            'pending_days' => 'integer',
        ];
    }

    public function getRemainingDaysAttribute(): int
    {
        return ($this->quota_days + $this->carry_over_days) - $this->used_days - $this->pending_days;
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }
}
