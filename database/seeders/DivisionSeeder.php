<?php

namespace Database\Seeders;

use App\Models\Division;
use Illuminate\Database\Seeder;

class DivisionSeeder extends Seeder
{
    public function run(): void
    {
        $divisions = [
            ['name' => 'Operations', 'code' => 'OPS', 'description' => 'Operasional dan produksi'],
            ['name' => 'Finance', 'code' => 'FIN', 'description' => 'Keuangan dan akuntansi'],
            ['name' => 'Human Resources', 'code' => 'HR', 'description' => 'Sumber daya manusia'],
            ['name' => 'Engineering', 'code' => 'ENG', 'description' => 'Teknik dan pengembangan'],
            ['name' => 'Marketing', 'code' => 'MKT', 'description' => 'Pemasaran dan komunikasi'],
        ];

        foreach ($divisions as $division) {
            Division::create($division);
        }
    }
}
