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
        Schema::create('kategori_k_p_i_s', function (Blueprint $table) {
            $table->id();
            $table->string('judul_kategori');
            $table->string('tipe_kategori');
            $table->integer('bobot');
            $table->string('level');
            $table->string('kode_kategori');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kategori_k_p_i_s');
    }
};
