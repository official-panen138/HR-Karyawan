<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'code' => 'annual',
                'name' => 'Cuti Tahunan',
                'default_quota_days' => 12,
                'is_paid' => true,
                'requires_document' => false,
                'allow_carry_over' => true,
                'max_carry_over_days' => 6,
                'min_working_months' => 12,
            ],
            [
                'code' => 'sick',
                'name' => 'Cuti Sakit',
                'default_quota_days' => null,
                'is_paid' => true,
                'requires_document' => true,
                'document_deadline_hours' => 48,
            ],
            [
                'code' => 'maternity',
                'name' => 'Cuti Melahirkan',
                'default_quota_days' => 90,
                'is_paid' => true,
                'requires_document' => true,
            ],
            [
                'code' => 'paternity',
                'name' => 'Cuti Ayah',
                'default_quota_days' => 2,
                'is_paid' => true,
                'requires_document' => true,
            ],
            [
                'code' => 'emergency',
                'name' => 'Cuti Darurat',
                'default_quota_days' => 3,
                'is_paid' => true,
                'requires_document' => true,
                'document_deadline_hours' => 24,
                'min_working_months' => 0,
            ],
            [
                'code' => 'marriage',
                'name' => 'Cuti Menikah',
                'default_quota_days' => 3,
                'is_paid' => true,
                'requires_document' => true,
            ],
            [
                'code' => 'unpaid',
                'name' => 'Cuti Tanpa Gaji',
                'default_quota_days' => null,
                'is_paid' => false,
                'requires_document' => false,
            ],
        ];

        foreach ($types as $type) {
            LeaveType::create($type);
        }
    }
}
