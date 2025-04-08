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
        Schema::create('analisisrkmmingguans', function (Blueprint $table) {
            $table->id();
            $table->string('id_kelasanalisis');
            $table->string('nama_materi');
            $table->string('nett_penjualan');
            $table->decimal('fixcost', 15, 2, false);
            $table->decimal('profit', 15, 2, false);
            $table->string('tahun');
            $table->string('bulan');
            $table->string('minggu');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analisisrkmmingguans');
    }
};
