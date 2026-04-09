<?php

namespace App\Models;

use App\Enums\LeaveRequestStatus;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class LeaveRequest extends Model
{
    use HasUuid, LogsActivity;

    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'total_days',
        'reason',
        'status',
        'is_emergency',
        'r2_key',
        'contact_during_leave',
        'handover_notes',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => LeaveRequestStatus::class,
            'start_date' => 'date',
            'end_date' => 'date',
            'total_days' => 'integer',
            'is_emergency' => 'boolean',
            'submitted_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'total_days', 'start_date', 'end_date'])
            ->logOnlyDirty();
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(LeaveApproval::class);
    }
}
