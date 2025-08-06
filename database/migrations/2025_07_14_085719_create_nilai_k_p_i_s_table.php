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
        Schema::create('nilai_k_p_i_s', function (Blueprint $table) {
            $table->id();
            $table->integer('id_evaluator');
            $table->integer('id_evaluated');
            $table->string('kode_form');
            $table->string('kode_kategori');
            $table->string('name_variabel');
            $table->text('pesan')->nullable();
            $table->integer('nilai')->nullable();
            $table->string('jenis_penilaian');
            $table->boolean('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nilai_k_p_i_s');
    }
};
