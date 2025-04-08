<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesTableSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'Komisaris',
            'Direktur Utama',
            'Direktur',
            'Education Manager',
            'Instruktur',
            'Technical Support',
            'GM',
            'SPV Sales',
            'Adm Sales',
            'Sales',
            'Tim Digital',
            'Finance & Accounting',
            'HRD',
            'Customer Care',
            'Office Boy',
            'Driver',
            'Programmer',
            'Office Manager',
            'Admin Holding',
            'Koordinator Office',
            'Super Admin'
         ];
         
         foreach ($permissions as $permission) {
              Role::create(['name' => $permission]);
         }
    }
}
