<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('detail_target_k_p_i_s') && Schema::hasColumn('detail_target_k_p_i_s', 'manual_value')) {
            Schema::table('detail_target_k_p_i_s', function (Blueprint $table) {
                $table->string('manual_value', 100)->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('detail_target_k_p_i_s') && Schema::hasColumn('detail_target_k_p_i_s', 'manual_value')) {
            Schema::table('detail_target_k_p_i_s', function (Blueprint $table) {
                $table->string('manual_value', 100)->change();
            });
        }
    }
};
