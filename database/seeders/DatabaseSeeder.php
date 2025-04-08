<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\karyawanSeeders;
use Database\Seeders\userSeeders;
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(PermissionTableSeeder::class);
        // $this->call(userSeeders::class);
        // $this->call(karyawanSeeders::class);
    }
}
