<?php

namespace App\Http\Requests;

use App\Enums\Religion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('employees.create');
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:150'],
            'religion' => ['required', Rule::enum(Religion::class)],
            'birth_date' => ['required', 'date'],
            'bank_account' => ['nullable', 'string', 'max:30'],
            'bank_name' => ['nullable', 'string', 'max:50'],
            'joined_at' => ['required', 'date'],
            'initial_salary' => ['required', 'numeric', 'min:0'],
            'current_salary' => ['required', 'numeric', 'min:0'],
            'position_id' => ['required', 'uuid', 'exists:positions,id'],
            'division_id' => ['required', 'uuid', 'exists:divisions,id'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ];
    }
}
