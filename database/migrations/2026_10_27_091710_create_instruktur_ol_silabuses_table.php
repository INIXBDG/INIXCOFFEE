<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instruktur_ol_silabus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instruktur_ol_id')->constrained('instruktur_ols')->cascadeOnDelete();
            $table->string('judul');
            $table->string('keterangan', 500)->nullable();
            $table->string('file_path');
            $table->string('file_nama_asli')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instruktur_ol_silabus');
    }
};