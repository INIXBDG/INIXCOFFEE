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
        if (Schema::hasTable('r_k_m_s')) {
            Schema::table('r_k_m_s', function (Blueprint $table) {
                if (!Schema::hasColumn('r_k_m_s', 'hide_materi')) {
                    $table->boolean('hide_materi')->default(false)->after('hide');
                }
                if (!Schema::hasColumn('r_k_m_s', 'hide_perusahaan')) {
                    $table->boolean('hide_perusahaan')->default(false)->after('hide');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('r_k_m_s', function (Blueprint $table) {
            $table->dropColumn('hide_materi');
            $table->dropColumn('hide_perusahaan');
        });
    }
};
