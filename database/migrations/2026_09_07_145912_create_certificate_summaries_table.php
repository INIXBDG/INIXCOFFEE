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
        Schema::create('certificate_summaries', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['Reg Digital', 'Webinar'])->nullable();
            $table->text('no_sertifikat')->nullable();
            $table->string('nama_peserta', 255)->nullable();
            $table->text('perusahaan')->nullable();
            $table->text('materi')->nullable();
            $table->date('awal_training')->nullable();
            $table->date('akhir_training')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificate_summaries');
    }
};
