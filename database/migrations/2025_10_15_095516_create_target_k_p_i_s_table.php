<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('target_k_p_i_s', function (Blueprint $table) {
            $table->id();
            $table->integer('id_pembuat');
            $table->string('asistant_route')->nullable();
            $table->text('judul');
            $table->text('deskripsi')->nullable();
            $table->string('jabatan');
            $table->string('divisi');
            $table->string('jangka_target');
            $table->text('detail_jangka')->nullable();
            $table->string('tipe_target');
            $table->bigInteger('nilai_target');
            $table->boolean('status');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('target_k_p_i_s');
    }
};
