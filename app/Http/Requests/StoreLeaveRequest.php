<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('leave.create');
    }

    public function rules(): array
    {
        return [
            'leave_type_id' => ['required', 'uuid', 'exists:leave_types,id'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['nullable', 'string', 'max:1000'],
            'is_emergency' => ['sometimes', 'boolean'],
            'contact_during_leave' => ['nullable', 'string', 'max:100'],
            'handover_notes' => ['nullable', 'string'],
            'file' => ['nullable', 'file', 'max:10240'],
        ];
    }
}
