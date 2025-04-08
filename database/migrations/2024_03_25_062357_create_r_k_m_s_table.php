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
        Schema::create('r_k_m_s', function (Blueprint $table) {
            $table->id();
            $table->string('sales_key');
            $table->string('materi_key');
            $table->string('perusahaan_key');
            $table->string('harga_jual');
            $table->string('pax');
            $table->string('isi_pax');
            $table->date('tanggal_awal');
            $table->date('tanggal_akhir');
            $table->string('metode_kelas')->nullable();
            $table->string('event')->nullable();
            $table->string('ruang')->nullable();
            $table->string('instruktur_key')->nullable();
            $table->string('instruktur_key2')->nullable();
            $table->string('asisten_key')->nullable();
            $table->enum('status', ['0', '1', '2']);//0 merah 1 biru 2 hitam
            $table->enum('exam', ['0', '1']);//0 tidak aktif 1 aktif
            $table->enum('authorize', ['0', '1']);//0 tidak aktif 1 aktif
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('r_k_m_s');
    }
};
