<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lsp_asesors', function (Blueprint $table) {
            $table->id();
            $table->string('nama_lengkap');
            $table->text('alamat')->nullable();
            $table->string('no_telp')->nullable();
            $table->string('email')->nullable();
            $table->string('npwp')->nullable();
            $table->string('ktp')->nullable();
            $table->timestamps();
        });

        // Relasi OPSIONAL asesor <-> LSP/LPK (many-to-many).
        // Asesor boleh tidak punya LSP, boleh juga mengawasi banyak LSP/LPK.
        Schema::create('asesor_lsp', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asesor_id')->constrained('lsp_asesors')->cascadeOnDelete();
            $table->foreignId('lsp_id')->constrained('lsps')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['asesor_id', 'lsp_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asesor_lsp');
        Schema::dropIfExists('lsp_asesors');
    }
};
