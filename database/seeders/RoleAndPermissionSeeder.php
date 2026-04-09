<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $modules = [
            'employees' => ['view', 'create', 'update', 'delete'],
            'documents' => ['view', 'create', 'update', 'delete', 'download'],
            'attendance' => ['view', 'check_in', 'check_out', 'break', 'report'],
            'shifts' => ['view', 'create', 'update', 'delete', 'assign'],
            'leave' => ['view', 'create', 'update', 'delete', 'approve'],
            'ip_whitelist' => ['view', 'create', 'update', 'delete'],
            'feature_flags' => ['view', 'create', 'update', 'delete'],
            'users' => ['view', 'create', 'update', 'delete'],
            'roles' => ['view', 'create', 'update', 'delete'],
            'reports' => ['view', 'export'],
        ];

        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                Permission::create(['name' => "{$module}.{$action}"]);
            }
        }

        // Super Admin — semua permission
        $superAdmin = Role::create(['name' => 'super_admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // HR Admin
        $hrAdmin = Role::create(['name' => 'hr_admin']);
        $hrAdmin->givePermissionTo([
            'employees.view', 'employees.create', 'employees.update',
            'documents.view', 'documents.create', 'documents.update', 'documents.download',
            'attendance.view', 'attendance.report',
            'shifts.view', 'shifts.create', 'shifts.update', 'shifts.assign',
            'leave.view', 'leave.create', 'leave.update', 'leave.approve',
            'ip_whitelist.view', 'ip_whitelist.create', 'ip_whitelist.update',
            'users.view', 'users.create', 'users.update',
            'reports.view', 'reports.export',
        ]);

        // Division Manager
        $divManager = Role::create(['name' => 'div_manager']);
        $divManager->givePermissionTo([
            'employees.view',
            'documents.view', 'documents.download',
            'attendance.view', 'attendance.check_in', 'attendance.check_out', 'attendance.break', 'attendance.report',
            'shifts.view',
            'leave.view', 'leave.create', 'leave.approve',
            'reports.view', 'reports.export',
        ]);

        // Supervisor
        $supervisor = Role::create(['name' => 'supervisor']);
        $supervisor->givePermissionTo([
            'employees.view',
            'documents.view',
            'attendance.view', 'attendance.check_in', 'attendance.check_out', 'attendance.break', 'attendance.report',
            'shifts.view',
            'leave.view', 'leave.create', 'leave.approve',
            'reports.view',
        ]);

        // Staff
        $staff = Role::create(['name' => 'staff']);
        $staff->givePermissionTo([
            'attendance.view', 'attendance.check_in', 'attendance.check_out', 'attendance.break',
            'leave.view', 'leave.create',
            'documents.view',
        ]);
    }
}
