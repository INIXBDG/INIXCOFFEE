<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('absensi_karyawans', function (Blueprint $table) {
            $table->index(['tanggal', 'id_karyawan'], 'idx_absensi_tgl_id');
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->index(['created_at'], 'idx_tickets_created_at');
        });

        Schema::table('administrasi_karyawans', function (Blueprint $table) {
            $table->index(['dateline'], 'idx_admin_dateline');
        });

        Schema::table('r_k_m_s', function (Blueprint $table) {
            $table->index(['status', 'tanggal_awal'], 'idx_rkms_status_tgl');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absensi_karyawans', function (Blueprint $table) {
            $table->dropIndex('idx_absensi_tgl_id');
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex('idx_tickets_created_at');
        });

        Schema::table('administrasi_karyawans', function (Blueprint $table) {
            $table->dropIndex('idx_admin_dateline');
        });

        Schema::table('r_k_m_s', function (Blueprint $table) {
            $table->dropIndex('idx_rkms_status_tgl');
        });
    }
};
