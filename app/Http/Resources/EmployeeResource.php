<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'religion' => $this->religion,
            'birth_date' => $this->birth_date?->format('Y-m-d'),
            'bank_account' => $this->bank_account,
            'bank_name' => $this->bank_name,
            'joined_at' => $this->joined_at?->format('Y-m-d'),
            'initial_salary' => $this->initial_salary,
            'current_salary' => $this->current_salary,
            'status' => $this->status,
            'photo_path' => $this->photo_path,
            'position' => new PositionResource($this->whenLoaded('position')),
            'division' => new DivisionResource($this->whenLoaded('division')),
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'email' => $this->user->email,
            ]),
            'created_at' => $this->created_at,
        ];
    }
}
