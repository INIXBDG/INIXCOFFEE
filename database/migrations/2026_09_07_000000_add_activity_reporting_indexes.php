<?php

use App\Support\MigrationHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        MigrationHelper::addIndexIfNotExists('aktivitas', [DB::raw('id_sales(50)'), 'waktu_aktivitas'], 'idx_aktivitas_sales_waktu');
        MigrationHelper::addIndexIfNotExists('aktivitas', [DB::raw('id_sales(50)'), 'aktivitas'], 'idx_aktivitas_sales_aktivitas');
        MigrationHelper::addIndexIfNotExists('aktivitas', 'created_at', 'idx_aktivitas_created');
        MigrationHelper::addIndexIfNotExists('aktivitas', 'id_peserta', 'idx_aktivitas_peserta');

        MigrationHelper::addIndexIfNotExists('target_activities', 'id_sales', 'idx_target_activities_sales');
    }

    public function down(): void
    {
        MigrationHelper::dropIndexIfExists('target_activities', 'idx_target_activities_sales');

        MigrationHelper::dropIndexIfExists('aktivitas', 'idx_aktivitas_sales_waktu');
        MigrationHelper::dropIndexIfExists('aktivitas', 'idx_aktivitas_sales_aktivitas');
        MigrationHelper::dropIndexIfExists('aktivitas', 'idx_aktivitas_created');
        MigrationHelper::dropIndexIfExists('aktivitas', 'idx_aktivitas_peserta');
    }
};
