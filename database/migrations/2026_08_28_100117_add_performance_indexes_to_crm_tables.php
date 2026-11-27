<?php

use App\Support\MigrationHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Index tabel aktivitas
        // waktu_aktivitas (DATE) & aktivitas (ENUM) di-index normal
        // id_sales (TEXT) wajib menggunakan prefix panjang karakter
        MigrationHelper::addIndexIfNotExists('aktivitas', ['waktu_aktivitas', DB::raw('id_sales(50)'), 'aktivitas'], 'idx_aktiv_waktu_sales_tipe');

        // 2. Index tabel peluangs
        MigrationHelper::addIndexIfNotExists('peluangs', 'merah', 'idx_peluang_merah');
        MigrationHelper::addIndexIfNotExists('peluangs', 'lost', 'idx_peluang_lost');
        MigrationHelper::addIndexIfNotExists('peluangs', 'created_at', 'idx_peluang_created');

        // 3. Index tabel r_k_m_s
        MigrationHelper::addIndexIfNotExists('r_k_m_s', ['status', 'materi_key'], 'idx_rkms_status_materi');

        // 4. Index tabel perusahaans
        MigrationHelper::addIndexIfNotExists('perusahaans', ['sales_key', 'lokasi'], 'idx_perusahaan_sales_lokasi');
        MigrationHelper::addIndexIfNotExists('perusahaans', 'kategori_perusahaan', 'idx_perusahaan_kategori');

        // 5. Index tabel materis
        MigrationHelper::addIndexIfNotExists('materis', 'nama_materi', 'idx_materis_nama');
        MigrationHelper::addIndexIfNotExists('materis', 'kategori_materi', 'idx_materis_kategori');
        MigrationHelper::addIndexIfNotExists('materis', 'vendor', 'idx_materis_vendor');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        MigrationHelper::dropIndexIfExists('aktivitas', 'idx_aktiv_waktu_sales_tipe');

        MigrationHelper::dropIndexIfExists('peluangs', 'idx_peluang_merah');
        MigrationHelper::dropIndexIfExists('peluangs', 'idx_peluang_lost');
        MigrationHelper::dropIndexIfExists('peluangs', 'idx_peluang_created');

        MigrationHelper::dropIndexIfExists('r_k_m_s', 'idx_rkms_status_materi');

        MigrationHelper::dropIndexIfExists('perusahaans', 'idx_perusahaan_sales_lokasi');
        MigrationHelper::dropIndexIfExists('perusahaans', 'idx_perusahaan_kategori');

        MigrationHelper::dropIndexIfExists('materis', 'idx_materis_nama');
        MigrationHelper::dropIndexIfExists('materis', 'idx_materis_kategori');
        MigrationHelper::dropIndexIfExists('materis', 'idx_materis_vendor');
    }
};
