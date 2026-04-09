<?php

namespace App\Models;

use App\Enums\BreakCategory;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BreakLog extends Model
{
    use HasUuid;

    protected $fillable = [
        'attendance_id',
        'category',
        'started_at',
        'ended_at',
        'duration_minutes',
        'started_ip',
    ];

    protected function casts(): array
    {
        return [
            'category' => BreakCategory::class,
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'duration_minutes' => 'integer',
        ];
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }
}
