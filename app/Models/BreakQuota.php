<?php

namespace App\Models;

use App\Enums\BreakCategory;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BreakQuota extends Model
{
    use HasUuid;

    protected $fillable = [
        'division_id',
        'shift_id',
        'category',
        'quota_minutes',
        'global_minutes_limit',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'category' => BreakCategory::class,
            'quota_minutes' => 'integer',
            'global_minutes_limit' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }
}
