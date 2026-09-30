<?php

namespace Database\Seeders;

use App\Models\DashboardLayout;
use Illuminate\Database\Seeder;

class DashboardLayoutSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sections = [
            'karyawan',
            'peserta',
            'itsm',
            'rkm',
            'finance',
            'performance',
            'education',
            'office',
            'crm',
            'management',
            'project',
        ];

        foreach ($sections as $index => $key) {
            DashboardLayout::updateOrCreate(
                ['section_key' => $key],
                ['sort_order' => $index + 1]
            );
        }
    }
}
