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
        Schema::create('kategori_daftar_tugas', function (Blueprint $table) {
            $table->id();
            $table->string('Jabatan_Pembuat');
            $table->enum('Tipe', ['Harian', 'Mingguan', 'Bulanan', 'Quartal', 'Semester', 'Tahunan']);
            $table->string('judul_kategori');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kategori_daftar_tugas');
    }
};
