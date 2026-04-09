<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'doc_type' => $this->doc_type,
            'doc_number' => $this->doc_number,
            'issued_at' => $this->issued_at?->format('Y-m-d'),
            'expired_at' => $this->expired_at?->format('Y-m-d'),
            'holder' => $this->holder,
            'renewal_status' => $this->renewal_status,
            'notes' => $this->notes,
            'status' => $this->status,
            'has_file' => !empty($this->r2_key),
            'employee' => new EmployeeResource($this->whenLoaded('employee')),
        ];
    }
}
