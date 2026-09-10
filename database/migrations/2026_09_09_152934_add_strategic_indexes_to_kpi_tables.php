<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('target_k_p_i_s', function (Blueprint $table) {
            $table->index('created_at', 'idx_target_kpi_created_at');
        });

        Schema::table('detail_target_k_p_i_s', function (Blueprint $table) {
            $table->index('id_targetKPI', 'idx_detail_target_kpi_id_target');
            $table->index('id_data_target', 'idx_detail_target_kpi_id_data');
            
            $table->index(['id_targetKPI', 'divisi', 'jabatan'], 'idx_detail_target_kpi_composite');
        });

        Schema::table('detail_person_k_p_i_s', function (Blueprint $table) {
            $table->index('id_target', 'idx_detail_person_kpi_id_target');
            $table->index('id_karyawan', 'idx_detail_person_kpi_id_karyawan');
            
            $table->index(['id_target', 'id_karyawan'], 'idx_detail_person_kpi_composite');
        });

        Schema::table('data_targets', function (Blueprint $table) {
            $table->index('asistant_route', 'idx_data_targets_asistant_route');
        });

        Schema::table('karyawans', function (Blueprint $table) {
            $table->index(['status_aktif', 'jabatan', 'divisi'], 'idx_karyawan_status_jabatan_divisi');
        });
    }

    public function down(): void
    {
        Schema::table('target_k_p_i_s', function (Blueprint $table) {
            $table->dropIndex('idx_target_kpi_created_at');
        });

        Schema::table('detail_target_k_p_i_s', function (Blueprint $table) {
            $table->dropIndex('idx_detail_target_kpi_id_target');
            $table->dropIndex('idx_detail_target_kpi_id_data');
            $table->dropIndex('idx_detail_target_kpi_composite');
        });

        Schema::table('detail_person_k_p_i_s', function (Blueprint $table) {
            $table->dropIndex('idx_detail_person_kpi_id_target');
            $table->dropIndex('idx_detail_person_kpi_id_karyawan');
            $table->dropIndex('idx_detail_person_kpi_composite');
        });

        Schema::table('data_targets', function (Blueprint $table) {
            $table->dropIndex('idx_data_targets_asistant_route');
        });

        Schema::table('karyawans', function (Blueprint $table) {
            $table->dropIndex('idx_karyawan_status_jabatan_divisi');
        });
    }
};