<?php

namespace App\Models;

use App\Enums\EmployeeStatus;
use App\Enums\Religion;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Employee extends Model
{
    use HasUuid, LogsActivity;

    protected $fillable = [
        'full_name',
        'religion',
        'birth_date',
        'bank_account',
        'bank_name',
        'joined_at',
        'initial_salary',
        'current_salary',
        'position_id',
        'division_id',
        'status',
        'photo_path',
    ];

    protected function casts(): array
    {
        return [
            'religion' => Religion::class,
            'status' => EmployeeStatus::class,
            'birth_date' => 'date',
            'joined_at' => 'date',
            'initial_salary' => 'decimal:2',
            'current_salary' => 'decimal:2',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['full_name', 'current_salary', 'status', 'position_id', 'division_id'])
            ->logOnlyDirty();
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', EmployeeStatus::ACTIVE);
    }

    public function scopeForDivision(Builder $query, string $divisionId): Builder
    {
        return $query->where('division_id', $divisionId);
    }

    // Relationships
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'employee_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function salaryHistories(): HasMany
    {
        return $this->hasMany(SalaryHistory::class);
    }

    public function employeeShifts(): HasMany
    {
        return $this->hasMany(EmployeeShift::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function leaveBalances(): HasMany
    {
        return $this->hasMany(EmployeeLeaveBalance::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }
}
