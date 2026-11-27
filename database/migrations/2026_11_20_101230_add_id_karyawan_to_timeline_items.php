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
        if (Schema::hasTable('timeline_items')) {
            Schema::table('timeline_items', function (Blueprint $table) {
                if (!Schema::hasColumn('timeline_items', 'id_karyawan')) {
                    $table->unsignedBigInteger('id_karyawan')->nullable()->after('year_mapping_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('timeline_items', function (Blueprint $table) {
            $table->dropColumn('id_karyawan');
        });
    }
};
