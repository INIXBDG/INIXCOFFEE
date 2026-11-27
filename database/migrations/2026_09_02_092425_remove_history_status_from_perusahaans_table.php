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
        if (Schema::hasTable('perusahaans') && Schema::hasColumn('perusahaans', 'history_status')) {
            Schema::table('perusahaans', function (Blueprint $table) {
                $table->dropColumn('history_status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('perusahaans') && !Schema::hasColumn('perusahaans', 'history_status')) {
            Schema::table('perusahaans', function (Blueprint $table) {
                // Menggunakan tipe text atau json sesuai dengan implementasi sebelumnya
                $table->text('history_status')->nullable();
            });
        }
    }
};
