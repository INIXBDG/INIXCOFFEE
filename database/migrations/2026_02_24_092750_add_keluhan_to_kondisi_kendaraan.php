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
        if (Schema::hasTable('kondisi_kendaraans')) {
            Schema::table('kondisi_kendaraans', function (Blueprint $table) {
                if (!Schema::hasColumn('kondisi_kendaraans', 'keluhan')) {
                    $table->text('keluhan')->nullable()->after('tanggal_pemeriksaan')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kondisi_kendaraans', function (Blueprint $table) {
            //
        });
    }
};
