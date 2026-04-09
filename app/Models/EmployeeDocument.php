<?php

namespace App\Models;

use App\Enums\DocHolder;
use App\Enums\DocType;
use App\Enums\RenewalStatus;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDocument extends Model
{
    use HasUuid;

    protected $fillable = [
        'employee_id',
        'doc_type',
        'doc_number',
        'issued_at',
        'expired_at',
        'r2_key',
        'holder',
        'renewal_status',
        'notes',
        'reminded_at_90',
        'reminded_at_30',
        'reminded_at_7',
    ];

    protected function casts(): array
    {
        return [
            'doc_type' => DocType::class,
            'holder' => DocHolder::class,
            'renewal_status' => RenewalStatus::class,
            'issued_at' => 'date',
            'expired_at' => 'date',
            'reminded_at_90' => 'datetime',
            'reminded_at_30' => 'datetime',
            'reminded_at_7' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function getStatusAttribute(): string
    {
        if (!$this->expired_at) {
            return 'no_expiry';
        }

        $daysUntilExpiry = now()->diffInDays($this->expired_at, false);

        if ($daysUntilExpiry < 0) return 'expired';
        if ($daysUntilExpiry <= 7) return 'critical';
        if ($daysUntilExpiry <= 30) return 'warning';
        if ($daysUntilExpiry <= 90) return 'notice';

        return 'valid';
    }
}
