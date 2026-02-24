<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel ini WAJIB ada untuk menampung hubungan Lab <-> Materi
        Schema::create('lab_materi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_id')->constrained('labs')->onDelete('cascade');

            // Pastikan nama tabel materi Anda benar ('materis' atau 'materi')
            $table->foreignId('materi_id')->constrained('materis')->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_materi');
    }
};
