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
        MigrationHelper::addIndexIfNotExists('absensi_karyawans', ['tanggal', 'id_karyawan'], 'idx_absensi_tanggal_karyawan');
        MigrationHelper::addIndexIfNotExists('tickets', ['status', 'created_at'], 'idx_tickets_status_created');
        MigrationHelper::addIndexIfNotExists('administrasi_karyawans', 'dateline', 'idx_administrasi_dateline');
        MigrationHelper::addIndexIfNotExists('r_k_m_s', ['tanggal_awal', 'status'], 'idx_rkms_tgl_awal_status');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        MigrationHelper::dropIndexIfExists('absensi_karyawans', 'idx_absensi_tanggal_karyawan');
        MigrationHelper::dropIndexIfExists('tickets', 'idx_tickets_status_created');
        MigrationHelper::dropIndexIfExists('administrasi_karyawans', 'idx_administrasi_dateline');
        MigrationHelper::dropIndexIfExists('r_k_m_s', 'idx_rkms_tgl_awal_status');
    }
};
