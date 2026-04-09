<?php

namespace App\Models;

use App\Enums\AttendanceStatus;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attendance extends Model
{
    use HasUuid;

    protected $fillable = [
        'employee_id',
        'shift_id',
        'date',
        'check_in_at',
        'check_out_at',
        'check_in_ip',
        'check_out_ip',
        'late_minutes',
        'effective_minutes',
        'status',
        'leave_request_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => AttendanceStatus::class,
            'date' => 'date',
            'check_in_at' => 'datetime',
            'check_out_at' => 'datetime',
            'late_minutes' => 'integer',
            'effective_minutes' => 'integer',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function leaveRequest(): BelongsTo
    {
        return $this->belongsTo(LeaveRequest::class);
    }

    public function breakLogs(): HasMany
    {
        return $this->hasMany(BreakLog::class);
    }
}
