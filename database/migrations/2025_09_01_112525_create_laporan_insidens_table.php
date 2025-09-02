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
        Schema::create('laporan_insidens', function (Blueprint $table) {
            $table->id();
            $table->integer('pelapor');
            $table->string('kejadian');
            $table->text('deskripsi');
            $table->string('kategori');
            $table->date('tanggal_kejadian');
            $table->time('waktu_kejadian');
            $table->string('lampiran');
            $table->enum('status', ['Baru', 'Dalam Penanganan', 'Ditindaklanjuti', 'Selesai', 'Tidak Ditindaklanjuti', 'Dibatalkan'])->default('Baru');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_insidens');
    }
};
