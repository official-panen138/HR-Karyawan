<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    use HasUuid;

    protected $fillable = [
        'division_id',
        'name',
        'start_time',
        'end_time',
        'crosses_midnight',
        'color',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'crosses_midnight' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function employeeShifts(): HasMany
    {
        return $this->hasMany(EmployeeShift::class);
    }

    public function breakQuotas(): HasMany
    {
        return $this->hasMany(BreakQuota::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }
}
