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
        Schema::create('catatansouvenirs', function (Blueprint $table) {
            $table->id();
            $table->string('id_souvenir');
            $table->string('catatan');
            $table->string('stok_terakhir')->nullable();
            $table->string('stok_perubahan')->nullable();
            $table->string('stok_terbaru')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catatansouvenirs');
    }
};
