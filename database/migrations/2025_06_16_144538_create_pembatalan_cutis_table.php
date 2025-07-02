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
        Schema::create('pembatalan_cutis', function (Blueprint $table) {
            $table->id();
            $table->integer('id_karyawan');
            $table->integer('id_cuti');
            $table->string('bukti_gambar');
            $table->text('kronologi')->nullable();
            $table->string('tipe');
            $table->date('tanggal_awal');
            $table->date('tanggal_akhir');
            $table->string('durasi');
            $table->string('kontak')->nullable();
            $table->string('alasan')->nullable();
            $table->string('surat_sakit')->nullable();
            $table->string('alasan_approval')->nullable();
            $table->integer('approval');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembatalan_cutis');
    }
};
