<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SuperAdminRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Mencari role 'Programmer'
        $programmerRole = Role::where('name', 'Programmer')->first();

        // Mencari role 'Education Manager'
        $educationManagerRole = Role::where('name', 'Education Manager')->first();

        // Mencari permission 'Akses Development'
        $permission = Permission::where('name', 'Akses Development')->first();

        // Memastikan role dan permission ditemukan
        if ($programmerRole && $permission) {
            // Menyinkronkan permission dengan role 'Programmer'
            $programmerRole->syncPermissions([$permission->name]);
        } else {
            if (!$programmerRole) {
                echo "Role 'Programmer' tidak ditemukan.\n";
            }
            if (!$permission) {
                echo "Permission 'Akses Development' tidak ditemukan.\n";
            }
        }

        // Menyinkronkan permission dengan role 'Education Manager'
        if ($educationManagerRole && $permission) {
            $educationManagerRole->syncPermissions([$permission->name]);
        } else {
            if (!$educationManagerRole) {
                echo "Role 'Education Manager' tidak ditemukan.\n";
            }
            if (!$permission) {
                echo "Permission 'Akses Development' tidak ditemukan.\n";
            }
        }
    }
}
