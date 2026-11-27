<?php

use App\Support\MigrationHelper;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Jalankan migrasi.
     */
    public function up(): void
    {
        MigrationHelper::addIndexIfNotExists('peluangs', 'tahap', 'idx_peluangs_tahap');
        MigrationHelper::addIndexIfNotExists('peluangs', 'id_sales', 'idx_peluangs_id_sales');
        MigrationHelper::addIndexIfNotExists('peluangs', 'created_at', 'idx_peluangs_created_at');

        MigrationHelper::addIndexIfNotExists('peluang_histories', 'id_peluang', 'idx_peluang_histories_id_peluang');
    }

    /**
     * Kembalikan migrasi.
     */
    public function down(): void
    {
        MigrationHelper::dropIndexIfExists('peluangs', 'idx_peluangs_tahap');
        MigrationHelper::dropIndexIfExists('peluangs', 'idx_peluangs_id_sales');
        MigrationHelper::dropIndexIfExists('peluangs', 'idx_peluangs_created_at');

        MigrationHelper::dropIndexIfExists('peluang_histories', 'idx_peluang_histories_id_peluang');
    }
};
