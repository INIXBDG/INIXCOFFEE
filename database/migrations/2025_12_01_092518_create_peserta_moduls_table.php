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
        Schema::create('peserta_moduls', function (Blueprint $table) {
            $table->id();
            $table->integer('no_modul');            
            $table->integer('modul');
            $table->text('nama_peserta');
            $table->text('perusahaan_id');
            $table->text('email');
            $table->date('awal_training');
            $table->date('akhir_training');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peserta_moduls');
    }
};
