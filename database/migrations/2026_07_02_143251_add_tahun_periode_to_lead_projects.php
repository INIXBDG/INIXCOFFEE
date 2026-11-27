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
        if (Schema::hasTable('lead_projects')) {
            Schema::table('lead_projects', function (Blueprint $table) {
                if (!Schema::hasColumn('lead_projects', 'tahun_periode')) {
                    $table->integer('tahun_periode')->nullable()->after('estimasi_nilai');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lead_projects', function (Blueprint $table) {
            $table->dropColumn('tahun_periode');
        });
    }
};
