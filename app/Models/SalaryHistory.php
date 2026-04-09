<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryHistory extends Model
{
    use HasUuid;

    protected $fillable = [
        'employee_id',
        'old_salary',
        'new_salary',
        'effective_date',
        'reason',
        'changed_by',
    ];

    protected function casts(): array
    {
        return [
            'old_salary' => 'decimal:2',
            'new_salary' => 'decimal:2',
            'effective_date' => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function changedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
