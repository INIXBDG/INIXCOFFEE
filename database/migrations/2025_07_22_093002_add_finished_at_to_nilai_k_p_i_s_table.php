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
        if (Schema::hasTable('nilai_k_p_i_s')) {
            Schema::table('nilai_k_p_i_s', function (Blueprint $table) {
                if (!Schema::hasColumn('nilai_k_p_i_s', 'finished_at')) {
                    $table->timestamp('finished_at')->nullable()->after('updated_at');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nilai_k_p_i_s', function (Blueprint $table) {
            $table->dropColumn('finished_at');
        });
    }
};
