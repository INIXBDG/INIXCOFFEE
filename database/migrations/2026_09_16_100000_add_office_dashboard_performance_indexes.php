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
        if (Schema::hasTable('absensi_karyawans')) {
            Schema::table('absensi_karyawans', function (Blueprint $table) {
                $table->index(['tanggal', 'id_karyawan'], 'idx_absensi_tanggal_karyawan');
            });
        }

        if (Schema::hasTable('tickets')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->index(['status', 'created_at'], 'idx_tickets_status_created');
            });
        }

        if (Schema::hasTable('administrasi_karyawans')) {
            Schema::table('administrasi_karyawans', function (Blueprint $table) {
                $table->index('dateline', 'idx_administrasi_dateline');
            });
        }

        if (Schema::hasTable('rkms')) {
            Schema::table('rkms', function (Blueprint $table) {
                $table->index(['tanggal_awal', 'status'], 'idx_rkms_tgl_awal_status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('absensi_karyawans')) {
            Schema::table('absensi_karyawans', function (Blueprint $table) {
                $table->dropIndex('idx_absensi_tanggal_karyawan');
            });
        }

        if (Schema::hasTable('tickets')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->dropIndex('idx_tickets_status_created');
            });
        }

        if (Schema::hasTable('administrasi_karyawans')) {
            Schema::table('administrasi_karyawans', function (Blueprint $table) {
                $table->dropIndex('idx_administrasi_dateline');
            });
        }

        if (Schema::hasTable('rkms')) {
            Schema::table('rkms', function (Blueprint $table) {
                $table->dropIndex('idx_rkms_tgl_awal_status');
            });
        }
    }
};
