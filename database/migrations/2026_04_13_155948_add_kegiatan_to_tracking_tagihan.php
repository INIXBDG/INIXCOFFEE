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
        if (Schema::hasTable('tracking_tagihan_perusahaans')) {
            Schema::table('tracking_tagihan_perusahaans', function (Blueprint $table) {
                if (!Schema::hasColumn('tracking_tagihan_perusahaans', 'kegiatan')) {
                    $table->string('kegiatan')->nullable();
                }
                if (!Schema::hasColumn('tracking_tagihan_perusahaans', 'tipe')) {
                    $table->string('tipe')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tracking_tagihan_perusahaans', function (Blueprint $table) {
            //
        });
    }
};
