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
        Schema::create('tagihan_perusahaans', function (Blueprint $table) {
            $table->id();
            $table->string('kegiatan');
            $table->enum('tipe', ['bulanan', 'tahunan']);
            $table->decimal( 'nominal', 15, 2)->nullable();
            $table->date('tanggal_perkiraan_mulai');
            $table->date('tanggal_perkiraan_selesai')->nullable();
            $table->date('last_generate');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tagihan_perusahaans');
    }
};
