<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('shifts.create');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50'],
            'division_id' => ['nullable', 'uuid', 'exists:divisions,id'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'crosses_midnight' => ['sometimes', 'boolean'],
            'color' => ['sometimes', 'string', 'max:7'],
        ];
    }
}
