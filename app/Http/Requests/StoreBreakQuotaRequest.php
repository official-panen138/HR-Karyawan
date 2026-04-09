<?php

namespace App\Http\Requests;

use App\Enums\BreakCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBreakQuotaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('shifts.create');
    }

    public function rules(): array
    {
        return [
            'division_id' => ['nullable', 'uuid', 'exists:divisions,id'],
            'shift_id' => ['nullable', 'uuid', 'exists:shifts,id'],
            'category' => ['required', Rule::enum(BreakCategory::class)],
            'quota_minutes' => ['required', 'integer', 'min:1', 'max:120'],
            'global_minutes_limit' => ['sometimes', 'integer', 'min:1', 'max:480'],
        ];
    }
}
