<?php

namespace Database\Seeders;

use App\Models\BreakQuota;
use Illuminate\Database\Seeder;

class BreakQuotaSeeder extends Seeder
{
    public function run(): void
    {
        $quotas = [
            ['category' => 'smoke', 'quota_minutes' => 15, 'global_minutes_limit' => 60],
            ['category' => 'toilet', 'quota_minutes' => 10, 'global_minutes_limit' => 60],
            ['category' => 'go_out', 'quota_minutes' => 20, 'global_minutes_limit' => 60],
        ];

        foreach ($quotas as $quota) {
            BreakQuota::create($quota);
        }
    }
}
