<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asesor_riwayat', function (Blueprint $table) {
            $table->id();

            // NULL = orang di luar data asesor (instruktur eksternal) -> isi nama_manual
            $table->foreignId('asesor_id')->nullable()
                ->constrained('lsp_asesors')
                ->cascadeOnDelete();

            $table->enum('jenis', ['uji_kompetensi', 'mengajar', 'pembayaran']);

            $table->string('nama_manual')->nullable();
            $table->date('tanggal')->nullable();
            $table->decimal('harga', 15, 2)->nullable();
            $table->text('keterangan')->nullable();

            $table->timestamps();

            $table->index(['jenis', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asesor_riwayat');
    }
};
