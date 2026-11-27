<?php

use App\Support\MigrationHelper;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        MigrationHelper::addIndexIfNotExists('target_k_p_i_s', 'created_at', 'idx_target_kpi_created_at');

        MigrationHelper::addIndexIfNotExists('detail_target_k_p_i_s', 'id_targetKPI', 'idx_detail_target_kpi_id_target');
        MigrationHelper::addIndexIfNotExists('detail_target_k_p_i_s', 'id_data_target', 'idx_detail_target_kpi_id_data');
        MigrationHelper::addIndexIfNotExists('detail_target_k_p_i_s', ['id_targetKPI', 'divisi', 'jabatan'], 'idx_detail_target_kpi_composite');

        MigrationHelper::addIndexIfNotExists('detail_person_k_p_i_s', 'id_target', 'idx_detail_person_kpi_id_target');
        MigrationHelper::addIndexIfNotExists('detail_person_k_p_i_s', 'id_karyawan', 'idx_detail_person_kpi_id_karyawan');
        MigrationHelper::addIndexIfNotExists('detail_person_k_p_i_s', ['id_target', 'id_karyawan'], 'idx_detail_person_kpi_composite');

        MigrationHelper::addIndexIfNotExists('data_targets', 'asistant_route', 'idx_data_targets_asistant_route');

        MigrationHelper::addIndexIfNotExists('karyawans', ['status_aktif', 'jabatan', 'divisi'], 'idx_karyawan_status_jabatan_divisi');
    }

    public function down(): void
    {
        MigrationHelper::dropIndexIfExists('target_k_p_i_s', 'idx_target_kpi_created_at');

        MigrationHelper::dropIndexIfExists('detail_target_k_p_i_s', 'idx_detail_target_kpi_id_target');
        MigrationHelper::dropIndexIfExists('detail_target_k_p_i_s', 'idx_detail_target_kpi_id_data');
        MigrationHelper::dropIndexIfExists('detail_target_k_p_i_s', 'idx_detail_target_kpi_composite');

        MigrationHelper::dropIndexIfExists('detail_person_k_p_i_s', 'idx_detail_person_kpi_id_target');
        MigrationHelper::dropIndexIfExists('detail_person_k_p_i_s', 'idx_detail_person_kpi_id_karyawan');
        MigrationHelper::dropIndexIfExists('detail_person_k_p_i_s', 'idx_detail_person_kpi_composite');

        MigrationHelper::dropIndexIfExists('data_targets', 'idx_data_targets_asistant_route');

        MigrationHelper::dropIndexIfExists('karyawans', 'idx_karyawan_status_jabatan_divisi');
    }
};