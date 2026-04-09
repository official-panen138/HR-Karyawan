<?php

namespace Database\Seeders;

use App\Models\PublicHoliday;
use Illuminate\Database\Seeder;

class PublicHolidaySeeder extends Seeder
{
    public function run(): void
    {
        $year = (int) date('Y');

        $holidays = [
            ['date' => "{$year}-01-01", 'name' => 'Tahun Baru Masehi'],
            ['date' => "{$year}-01-27", 'name' => 'Tahun Baru Imlek'],
            ['date' => "{$year}-03-28", 'name' => 'Hari Raya Nyepi'],
            ['date' => "{$year}-03-29", 'name' => 'Wafat Isa Almasih'],
            ['date' => "{$year}-03-30", 'name' => 'Idul Fitri 1447H'],
            ['date' => "{$year}-03-31", 'name' => 'Idul Fitri 1447H'],
            ['date' => "{$year}-05-01", 'name' => 'Hari Buruh Internasional'],
            ['date' => "{$year}-05-12", 'name' => 'Hari Raya Waisak'],
            ['date' => "{$year}-05-29", 'name' => 'Kenaikan Isa Almasih'],
            ['date' => "{$year}-06-01", 'name' => 'Hari Lahir Pancasila'],
            ['date' => "{$year}-06-06", 'name' => 'Idul Adha 1447H'],
            ['date' => "{$year}-06-27", 'name' => 'Tahun Baru Islam 1448H'],
            ['date' => "{$year}-08-17", 'name' => 'Hari Kemerdekaan RI'],
            ['date' => "{$year}-09-05", 'name' => 'Maulid Nabi Muhammad SAW'],
            ['date' => "{$year}-12-25", 'name' => 'Hari Raya Natal'],
        ];

        foreach ($holidays as $holiday) {
            PublicHoliday::create([
                'date' => $holiday['date'],
                'name' => $holiday['name'],
                'year' => $year,
                'is_national' => true,
            ]);
        }
    }
}
