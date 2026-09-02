<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
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
        Schema::table('aktivitas', function (Blueprint $table) {
            $table->index(['waktu_aktivitas', DB::raw('id_sales(50)'), 'aktivitas'], 'idx_aktiv_waktu_sales_tipe');
        });

        // 2. Index tabel peluangs
        Schema::table('peluangs', function (Blueprint $table) {
            $table->index('merah', 'idx_peluang_merah');
            $table->index('lost', 'idx_peluang_lost');
            $table->index('created_at', 'idx_peluang_created');
        });

        // 3. Index tabel r_k_m_s
        // status (ENUM) & materi_key (VARCHAR) di-index normal
        Schema::table('r_k_m_s', function (Blueprint $table) {
            $table->index(['status', 'materi_key'], 'idx_rkms_status_materi');
        });

        // 4. Index tabel perusahaans
        // sales_key & lokasi (VARCHAR) di-index normal
        Schema::table('perusahaans', function (Blueprint $table) {
            $table->index(['sales_key', 'lokasi'], 'idx_perusahaan_sales_lokasi');
            $table->index('kategori_perusahaan', 'idx_perusahaan_kategori');
        });

        // 5. Index tabel materis
        // nama, kategori, dan vendor (VARCHAR) di-index normal
        Schema::table('materis', function (Blueprint $table) {
            $table->index('nama_materi', 'idx_materis_nama');
            $table->index('kategori_materi', 'idx_materis_kategori');
            $table->index('vendor', 'idx_materis_vendor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aktivitas', function (Blueprint $table) {
            $table->dropIndex('idx_aktiv_waktu_sales_tipe');
        });

        Schema::table('peluangs', function (Blueprint $table) {
            $table->dropIndex('idx_peluang_merah');
            $table->dropIndex('idx_peluang_lost');
            $table->dropIndex('idx_peluang_created');
        });

        Schema::table('r_k_m_s', function (Blueprint $table) {
            $table->dropIndex('idx_rkms_status_materi');
        });

        Schema::table('perusahaans', function (Blueprint $table) {
            $table->dropIndex('idx_perusahaan_sales_lokasi');
            $table->dropIndex('idx_perusahaan_kategori');
        });

        Schema::table('materis', function (Blueprint $table) {
            $table->dropIndex('idx_materis_nama');
            $table->dropIndex('idx_materis_kategori');
            $table->dropIndex('idx_materis_vendor');
        });
    }
};
