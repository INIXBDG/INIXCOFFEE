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
                if (!Schema::hasColumn('r_k_m_s', 'hide')) {
                    $table->boolean('hide')->default(false)->after('status');
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
            $table->dropColumn('hide');
        });
    }
};
