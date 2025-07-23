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
        Schema::create('form_penilaians', function (Blueprint $table) {
            $table->id();
            $table->string('nama_penilaian');
            $table->integer( 'id_karyawan');
            $table->string('kode_kategori');
            $table->string('kode_form');
            $table->string('quartal');
            $table->year('tahun');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_penilaians');
    }
};
