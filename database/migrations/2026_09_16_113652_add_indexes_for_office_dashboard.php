<?php

use App\Support\MigrationHelper;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        MigrationHelper::addIndexIfNotExists('absensi_karyawans', ['tanggal', 'id_karyawan'], 'idx_absensi_tgl_id');
        MigrationHelper::addIndexIfNotExists('tickets', ['created_at'], 'idx_tickets_created_at');
        MigrationHelper::addIndexIfNotExists('administrasi_karyawans', ['dateline'], 'idx_admin_dateline');
        MigrationHelper::addIndexIfNotExists('r_k_m_s', ['status', 'tanggal_awal'], 'idx_rkms_status_tgl');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        MigrationHelper::dropIndexIfExists('absensi_karyawans', 'idx_absensi_tgl_id');
        MigrationHelper::dropIndexIfExists('tickets', 'idx_tickets_created_at');
        MigrationHelper::dropIndexIfExists('administrasi_karyawans', 'idx_admin_dateline');
        MigrationHelper::dropIndexIfExists('r_k_m_s', 'idx_rkms_status_tgl');
    }
};
