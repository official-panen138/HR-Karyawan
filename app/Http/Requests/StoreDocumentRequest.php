<?php

namespace App\Http\Requests;

use App\Enums\DocHolder;
use App\Enums\DocType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('documents.create');
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'uuid', 'exists:employees,id'],
            'doc_type' => ['required', Rule::enum(DocType::class)],
            'doc_number' => ['nullable', 'string', 'max:60'],
            'issued_at' => ['nullable', 'date'],
            'expired_at' => ['nullable', 'date', 'after_or_equal:issued_at'],
            'holder' => ['sometimes', Rule::enum(DocHolder::class)],
            'notes' => ['nullable', 'string'],
            'file' => ['nullable', 'file', 'max:10240'],
        ];
    }
}
