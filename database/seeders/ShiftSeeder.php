<?php

namespace Database\Seeders;

use App\Models\Shift;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
{
    public function run(): void
    {
        $shifts = [
            [
                'name' => 'Morning',
                'start_time' => '07:00',
                'end_time' => '15:00',
                'crosses_midnight' => false,
                'color' => '#3B82F6',
            ],
            [
                'name' => 'Middle',
                'start_time' => '15:00',
                'end_time' => '23:00',
                'crosses_midnight' => false,
                'color' => '#F59E0B',
            ],
            [
                'name' => 'Night',
                'start_time' => '23:00',
                'end_time' => '07:00',
                'crosses_midnight' => true,
                'color' => '#6366F1',
            ],
        ];

        foreach ($shifts as $shift) {
            Shift::create($shift);
        }
    }
}
