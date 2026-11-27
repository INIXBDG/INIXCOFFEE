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
                if (!Schema::hasColumn('tracking_tagihan_perusahaans', 'tanggal_perkiraan_mulai')) {
                    $table->date('tanggal_perkiraan_mulai');
                }
                if (!Schema::hasColumn('tracking_tagihan_perusahaans', 'tanggal_perkiraan_selesai')) {
                    $table->date('tanggal_perkiraan_selesai')->nullable();
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
