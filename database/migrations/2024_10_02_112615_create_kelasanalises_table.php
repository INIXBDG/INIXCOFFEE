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
        Schema::create('kelasanalises', function (Blueprint $table) {
            $table->id();
            $table->string('id_rkm');
            $table->string('pax');
            $table->string('durasi');
            $table->decimal('total_harga_jual', 15)->nullable();
            $table->decimal('harga_modul_regular', 15)->nullable();
            $table->decimal('harga_modul_regular_dollar', 15, 2)->nullable();
            $table->decimal('kurs_dollar', 15, 2)->nullable();
            $table->decimal('biaya_modul_regular', 15, 2)->nullable();
            $table->decimal('biaya_modul_regular_dollar', 15, 2)->nullable();
            $table->decimal('makan_siang', 15, 2)->nullable();
            $table->decimal('coffee_break', 15, 2)->nullable();
            $table->decimal('konsumsi', 15, 2)->nullable();
            $table->decimal('souvenir_satu', 15, 2)->nullable();
            $table->decimal('souvenir', 15, 2)->nullable();
            $table->decimal('pa_hotel_akomodasi', 15, 2)->nullable();
            $table->decimal('pa_hotel', 15, 2)->nullable();
            $table->decimal('exam', 15, 2)->nullable();
            $table->decimal('pc_pax', 15, 2)->nullable();
            $table->string('pc_instruktur')->nullable();
            $table->decimal('pc', 15, 2)->nullable();
            $table->decimal('alat', 15, 2)->nullable();
            $table->decimal('fee_instruktur', 15, 2)->nullable();
            $table->decimal('total_fee_instruktur', 15, 2)->nullable();
            $table->decimal('nett_penjualan', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kelasanalises');
    }
};
