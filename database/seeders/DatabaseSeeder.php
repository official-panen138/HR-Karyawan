<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DivisionSeeder::class,
            PositionSeeder::class,
            RoleAndPermissionSeeder::class,
            ShiftSeeder::class,
            BreakQuotaSeeder::class,
            LeaveTypeSeeder::class,
            PublicHolidaySeeder::class,
            AdminUserSeeder::class,
            FeatureFlagSeeder::class,
        ]);
    }
}
