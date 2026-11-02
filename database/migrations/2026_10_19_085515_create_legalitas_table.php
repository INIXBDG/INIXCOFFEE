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
        Schema::create('legalitas', function (Blueprint $table) {
            $table->id();
            $table->string('nama_dokumen', 255);
            $table->string('path_dokumen');
            $table->text('nomor_dokumen');
            $table->date('tanggal_terbit');
            $table->integer('masa_berlaku');
            $table->enum('satuan_masa_berlaku', ['hari', 'bulan', 'tahun']);
            $table->date('tanggal_berakhir')->nullable();
            $table->enum('status', ['aktif', 'kadaluarsa'])->default('aktif');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('legalitas');
    }
};