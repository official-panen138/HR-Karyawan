<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveType extends Model
{
    use HasUuid;

    protected $fillable = [
        'code',
        'name',
        'default_quota_days',
        'is_paid',
        'requires_document',
        'document_deadline_hours',
        'allow_carry_over',
        'max_carry_over_days',
        'min_working_months',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'default_quota_days' => 'integer',
            'is_paid' => 'boolean',
            'requires_document' => 'boolean',
            'document_deadline_hours' => 'integer',
            'allow_carry_over' => 'boolean',
            'max_carry_over_days' => 'integer',
            'min_working_months' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function configs(): HasMany
    {
        return $this->hasMany(LeaveTypeConfig::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function balances(): HasMany
    {
        return $this->hasMany(EmployeeLeaveBalance::class);
    }
}
