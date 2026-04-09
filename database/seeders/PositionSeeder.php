<?php

namespace Database\Seeders;

use App\Models\Division;
use App\Models\Position;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        $divisions = Division::all()->keyBy('code');

        $positions = [
            ['name' => 'Operations Staff', 'code' => 'OPS-STF', 'division' => 'OPS', 'level' => 1],
            ['name' => 'Operations Supervisor', 'code' => 'OPS-SPV', 'division' => 'OPS', 'level' => 2],
            ['name' => 'Operations Manager', 'code' => 'OPS-MGR', 'division' => 'OPS', 'level' => 3],

            ['name' => 'Finance Staff', 'code' => 'FIN-STF', 'division' => 'FIN', 'level' => 1],
            ['name' => 'Finance Supervisor', 'code' => 'FIN-SPV', 'division' => 'FIN', 'level' => 2],
            ['name' => 'Finance Manager', 'code' => 'FIN-MGR', 'division' => 'FIN', 'level' => 3],

            ['name' => 'HR Staff', 'code' => 'HR-STF', 'division' => 'HR', 'level' => 1],
            ['name' => 'HR Supervisor', 'code' => 'HR-SPV', 'division' => 'HR', 'level' => 2],
            ['name' => 'HR Manager', 'code' => 'HR-MGR', 'division' => 'HR', 'level' => 3],

            ['name' => 'Engineer', 'code' => 'ENG-STF', 'division' => 'ENG', 'level' => 1],
            ['name' => 'Engineering Supervisor', 'code' => 'ENG-SPV', 'division' => 'ENG', 'level' => 2],
            ['name' => 'Engineering Manager', 'code' => 'ENG-MGR', 'division' => 'ENG', 'level' => 3],

            ['name' => 'Marketing Staff', 'code' => 'MKT-STF', 'division' => 'MKT', 'level' => 1],
            ['name' => 'Marketing Supervisor', 'code' => 'MKT-SPV', 'division' => 'MKT', 'level' => 2],
            ['name' => 'Marketing Manager', 'code' => 'MKT-MGR', 'division' => 'MKT', 'level' => 3],
        ];

        foreach ($positions as $pos) {
            Position::create([
                'name' => $pos['name'],
                'code' => $pos['code'],
                'division_id' => $divisions[$pos['division']]->id,
                'level' => $pos['level'],
            ]);
        }
    }
}
