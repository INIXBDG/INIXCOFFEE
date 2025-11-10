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
        Schema::create('certificates', function (Blueprint $table) {
            $table->id(); // Ini adalah bigint(20) UNSIGNED AUTO_INCREMENT
            $table->string('nomor_sertifikat')->unique();
            
            $table->unsignedBigInteger('rkm_id');
            $table->unsignedBigInteger('id_peserta');
            
            $table->string('nama_peserta');
            $table->string('nama_materi');
            $table->string('tanggal_pelatihan'); // Tipe VARCHAR (string)
            $table->string('pdf_path')->nullable(); // Kolom untuk menyimpan path PDF
            
            $table->timestamps(); // created_at dan updated_at

            // === FOREIGN KEY YANG BENAR ===

            // Menghubungkan rkm_id ke tabel r_k_m_s
            $table->foreign('rkm_id')
                  ->references('id')
                  ->on('r_k_m_s')
                  ->onDelete('cascade');

            // Menghubungkan id_peserta ke tabel pesertas
            $table->foreign('id_peserta')
                  ->references('id')
                  ->on('pesertas') // <-- Ini perbaikan dari error Anda
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};