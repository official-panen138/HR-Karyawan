<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class PublicHoliday extends Model
{
    use HasUuid;

    protected $fillable = [
        'date',
        'name',
        'year',
        'is_national',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'year' => 'integer',
            'is_national' => 'boolean',
        ];
    }
}
