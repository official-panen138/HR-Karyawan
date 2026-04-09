<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@company.com',
            'password' => Hash::make(env('ADMIN_DEFAULT_PASSWORD', 'password')),
            'email_verified_at' => now(),
        ]);

        $admin->assignRole('super_admin');
    }
}
