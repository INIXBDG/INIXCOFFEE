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
        Schema::create('catatan_client_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laporan_id')->constrained('laporan_harian_sales')->cascadeOnDelete();
            $table->string('nama_perusahaan');
            $table->string('kebutuhan')->nullable();
            $table->string('rekomendasi_silabus')->nullable();
            $table->string('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catatan_client_sales');
    }
};
