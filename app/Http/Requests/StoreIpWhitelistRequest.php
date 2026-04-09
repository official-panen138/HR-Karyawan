<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreIpWhitelistRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('ip_whitelist.create');
    }

    public function rules(): array
    {
        return [
            'ip_address' => ['required', 'ip'],
            'label' => ['required', 'string', 'max:80'],
            'division_id' => ['nullable', 'uuid', 'exists:divisions,id'],
        ];
    }
}
