<?php

use App\Support\MigrationHelper;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        MigrationHelper::addIndexIfNotExists('nilaifeedbacks', 'created_at', 'nilaifeedbacks_created_at_index');
        MigrationHelper::addIndexIfNotExists('nilaifeedbacks', 'id_rkm', 'nilaifeedbacks_id_rkm_index');
        MigrationHelper::addIndexIfNotExists('nilaifeedbacks', 'instruktur_id', 'nilaifeedbacks_instruktur_id_index');

        MigrationHelper::addIndexIfNotExists('r_k_m_s', 'instruktur_key', 'rkms_instruktur_key_index');
    }

    public function down(): void
    {
        MigrationHelper::dropIndexIfExists('nilaifeedbacks', 'nilaifeedbacks_created_at_index');
        MigrationHelper::dropIndexIfExists('nilaifeedbacks', 'nilaifeedbacks_id_rkm_index');
        MigrationHelper::dropIndexIfExists('nilaifeedbacks', 'nilaifeedbacks_instruktur_id_index');

        MigrationHelper::dropIndexIfExists('r_k_m_s', 'rkms_instruktur_key_index');
    }
};
