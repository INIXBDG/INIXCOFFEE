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
        if (Schema::hasTable('detail_person_k_p_i_s')) {
            Schema::table('detail_person_k_p_i_s', function (Blueprint $table) {
                if (!Schema::hasColumn('detail_person_k_p_i_s', 'presentase_kemampuan')) {
                    $table->string('presentase_kemampuan')->nullable()->after('id_karyawan');
                }
                if (!Schema::hasColumn('detail_person_k_p_i_s', 'presentase_standar')) {
                    $table->string('presentase_standar')->nullable()->after('presentase_kemampuan');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_person_k_p_i_s', function (Blueprint $table) {
            $table->dropColumn(['presentase_kemampuan', 'presentase_standar']);
        });
    }
};