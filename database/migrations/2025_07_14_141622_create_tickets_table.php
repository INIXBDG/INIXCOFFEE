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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->datetime('timestamp');
            $table->string('nama_karyawan');
            $table->string('divisi');
            $table->string('kategori');
            $table->string('keperluan');
            $table->text('detail_kendala');
            $table->date('tanggal_response')->nullable();
            $table->time('jam_response')->nullable();
            $table->string('pic')->nullable();
            $table->text('penanganan')->nullable();
            $table->enum('status', ['Menunggu', 'Di Proses', 'Selesai', 'Terkendala'])->default('Menunggu');
            $table->text('keterangan')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->time('jam_selesai')->nullable();
            $table->string('tingkat_kesulitan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
