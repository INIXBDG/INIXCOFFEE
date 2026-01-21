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
        Schema::create('kegiatans', function (Blueprint $table) {
            $table->id();
            $table->text('nama_kegiatan');
            $table->dateTime('waktu_kegiatan');
            $table->text('lama_kegiatan');
            $table->text('pic')->nullable();
            $table->enum('status', ['Diajukan', 'Menunggu', 'Approved', 'Pencairan', 'Selesai'])->default('Diajukan');
            $table->dateTime('menunggu')->nullable();
            $table->dateTime('approved')->nullable();
            $table->dateTime('pencairan')->nullable();
            $table->dateTime('selesai')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kegiatans');
    }
};
