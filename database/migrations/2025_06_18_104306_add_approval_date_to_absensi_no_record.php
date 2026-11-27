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
        if (Schema::hasTable('absensi_no_records')) {
            Schema::table('absensi_no_records', function (Blueprint $table) {
                if (!Schema::hasColumn('absensi_no_records', 'approval_date')) {
                    $table->timestamp('approval_date')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absensi_noRecord', function (Blueprint $table) {
            $table->dropColumn('date_approval');
        });
    }
};
