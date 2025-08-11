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
        Schema::create('share_forms', function (Blueprint $table) {
            $table->id();
            $table->integer('id_evaluator');
            $table->integer('id_evaluated');
            $table->string('divisi_evaluator');
            $table->string('kode_form');
            $table->text('jenis_penilaian');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('share_forms');
    }
};
