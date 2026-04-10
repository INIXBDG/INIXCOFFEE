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
        Schema::table('pickup_drivers', function (Blueprint $table) {
            $table->string('KM_awal')->nullable();
            $table->string('KM_akhir')->nullable();
            $table->string('tipe_perjalanan')->nullable();
        });

        Schema::table('perbaikan_kendaraans', function (Blueprint $table) {
            $table->date('tanggal_perbaikan')->nullable();
            $table->string('selesai_perbaikan')->nullable();
            $table->text('detail_perbaikan')->nullable();
            $table->string('document')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pickup_drivers', function (Blueprint $table) {
            //
        });
    }
};
