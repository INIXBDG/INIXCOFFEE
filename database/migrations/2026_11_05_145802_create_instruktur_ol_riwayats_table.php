<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('instruktur_ol_riwayats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instruktur_ol_id');
            $table->unsignedBigInteger('rkm_id');
            $table->timestamps();

            $table->unique(['instruktur_ol_id', 'rkm_id']);
            $table->index('rkm_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instruktur_ol_riwayats');
    }
};