<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Truncate tabel roles
        // Role::truncate();

        // Mendapatkan posisi unik dari pengguna
        $positions = User::pluck('jabatan')->unique();

        foreach ($positions as $position) {
            // Membuat role jika belum ada
            $role = Role::firstOrCreate(['name' => $position]);
        }

        // Mengaitkan role kepada pengguna berdasarkan jabatan mereka
        $users = User::all();

        foreach ($users as $user) {
            $roleName = $user->jabatan;
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $user->syncRoles([$role->name]);
            }
        }
    }
}
