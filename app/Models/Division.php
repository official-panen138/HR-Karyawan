<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Division extends Model
{
    use HasUuid;

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function positions(): HasMany
    {
        return $this->hasMany(Position::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(Shift::class);
    }

    public function breakQuotas(): HasMany
    {
        return $this->hasMany(BreakQuota::class);
    }

    public function leaveTypeConfigs(): HasMany
    {
        return $this->hasMany(LeaveTypeConfig::class);
    }

    public function ipWhitelist(): HasMany
    {
        return $this->hasMany(IpWhitelist::class);
    }
}
