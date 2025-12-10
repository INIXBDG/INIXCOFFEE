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
        Schema::create('nomor_moduls', function (Blueprint $table) {
            $table->id();
            $table->text('no_modul');
            $table->enum('type', ['Regular', 'Authorize']);
            $table->enum('status', ['Menunggu', 'Disetujui'])->default('Menunggu');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nomor_moduls');
    }
};
