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
        Schema::create('registexams', function (Blueprint $table) {
            $table->id();
            $table->string('id_peserta');
            $table->string('id_exam');
            $table->string('email');
            $table->string('kode_exam');
            $table->date('tanggal_exam');
            $table->time('pukul');
            $table->string('nama_perguruan_tinggi')->nullable();
            $table->string('alamat_perguruan_tinggi')->nullable();
            $table->string('jurusan')->nullable();
            $table->string('tahun_lulus')->nullable();
            $table->string('invoice')->nullable();
            $table->string('cc')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registexams');
    }
};
