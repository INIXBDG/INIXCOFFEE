<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('nilaifeedbacks')) {
            Schema::table('nilaifeedbacks', function (Blueprint $table) {
                $table->index('created_at', 'nilaifeedbacks_created_at_index');
                $table->index('id_rkm', 'nilaifeedbacks_id_rkm_index');

                if (Schema::hasColumn('nilaifeedbacks', 'instruktur_id')) {
                    $table->index('instruktur_id', 'nilaifeedbacks_instruktur_id_index');
                }
            });
        }

        if (Schema::hasTable('r_k_m_s') && Schema::hasColumn('r_k_m_s', 'instruktur_key')) {
            Schema::table('r_k_m_s', function (Blueprint $table) {
                $table->index('instruktur_key', 'rkms_instruktur_key_index');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('nilaifeedbacks')) {
            Schema::table('nilaifeedbacks', function (Blueprint $table) {
                $table->dropIndex('nilaifeedbacks_created_at_index');
                $table->dropIndex('nilaifeedbacks_id_rkm_index');

                if (Schema::hasColumn('nilaifeedbacks', 'instruktur_id')) {
                    $table->dropIndex('nilaifeedbacks_instruktur_id_index');
                }
            });
        }

        if (Schema::hasTable('r_k_m_s') && Schema::hasColumn('r_k_m_s', 'instruktur_key')) {
            Schema::table('r_k_m_s', function (Blueprint $table) {
                $table->dropIndex('rkms_instruktur_key_index');
            });
        }
    }
};
