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
        Schema::create('checkbarangs', function (Blueprint $table) {
            $table->id();
            $table->text('idbarang'); // Barang yang diperiksa
            $table->date('tanggal_pemeriksaan'); // Tanggal pemeriksaan
            $table->enum('interval', ['3bulan', '6bulan']); // Interval pemeriksaan
            $table->enum('kondisi', ['baik', 'rusak/bermasalah', 'sedang diperbaiki']); // Kondisi saat diperiksa
            $table->text('catatan')->nullable(); // Catatan pemeriksaan
            $table->text('inspector'); // Pemeriksa (HR)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checkbarangs');
    }
};
