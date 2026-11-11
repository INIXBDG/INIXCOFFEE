<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lsp_lpk_programs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('lsp_id')
                ->constrained('lsps')
                ->cascadeOnDelete();

            $table->enum('jenis', ['sertifikasi', 'pelatihan']);

            $table->string('nama_skema')->nullable();
            $table->string('jenis_skema')->nullable();
            $table->string('jenjang_kkni')->nullable();
            $table->string('pelaksanaan')->nullable();

            $table->decimal('harga_sertifikasi_per_pax', 15, 2)->nullable();
            $table->decimal('harga_pelatihan_per_hari', 15, 2)->nullable();

            $table->text('keterangan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lsp_lpk_programs');
    }
};
