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
        if (Schema::hasTable('analysis_reports')) {
            Schema::table('analysis_reports', function (Blueprint $table) {
                if (!Schema::hasColumn('analysis_reports', 'nilai')) {
                    $table->bigInteger('nilai')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('analysis_report', function (Blueprint $table) {
            //
        });
    }
};
