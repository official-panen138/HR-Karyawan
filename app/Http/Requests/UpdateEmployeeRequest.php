<?php

namespace App\Http\Requests;

use App\Enums\EmployeeStatus;
use App\Enums\Religion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('employees.update');
    }

    public function rules(): array
    {
        return [
            'full_name' => ['sometimes', 'string', 'max:150'],
            'religion' => ['sometimes', Rule::enum(Religion::class)],
            'birth_date' => ['sometimes', 'date'],
            'bank_account' => ['nullable', 'string', 'max:30'],
            'bank_name' => ['nullable', 'string', 'max:50'],
            'current_salary' => ['sometimes', 'numeric', 'min:0'],
            'position_id' => ['sometimes', 'uuid', 'exists:positions,id'],
            'division_id' => ['sometimes', 'uuid', 'exists:divisions,id'],
            'status' => ['sometimes', Rule::enum(EmployeeStatus::class)],
            'photo' => ['nullable', 'image', 'max:2048'],
        ];
    }
}
