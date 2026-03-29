<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migration.
     */
    public function up(): void
    {
        Schema::table('tracking_pengajuan_barangs', function (Blueprint $table) {
            $table->json('detail_perubahan')->nullable()->after('tracking');
        });
    }

    /**
     * Kembalikan migration.
     */
    public function down(): void
    {
        Schema::table('tracking_pengajuan_barangs', function (Blueprint $table) {
            $table->dropColumn('detail_perubahan');
        });
    }
};