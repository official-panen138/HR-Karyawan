<?php

namespace Database\Seeders;

use App\Models\FeatureFlag;
use Illuminate\Database\Seeder;

class FeatureFlagSeeder extends Seeder
{
    public function run(): void
    {
        $flags = [
            [
                'feature_key' => 'payroll_view',
                'label' => 'Lihat Data Payroll',
                'description' => 'Akses untuk melihat data penggajian',
                'enabled_for_roles' => ['super_admin', 'hr_admin'],
            ],
            [
                'feature_key' => 'doc_upload',
                'label' => 'Upload Dokumen',
                'description' => 'Akses untuk mengupload dokumen karyawan',
                'enabled_for_roles' => ['super_admin', 'hr_admin'],
            ],
            [
                'feature_key' => 'break_override',
                'label' => 'Override Kuota Break',
                'description' => 'Akses untuk override batas kuota break',
                'enabled_for_roles' => ['super_admin'],
            ],
            [
                'feature_key' => 'report_export',
                'label' => 'Export Laporan',
                'description' => 'Akses untuk mengexport laporan ke CSV/Excel',
                'enabled_for_roles' => ['super_admin', 'hr_admin', 'div_manager'],
            ],
        ];

        foreach ($flags as $flag) {
            FeatureFlag::create($flag);
        }
    }
}
