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
        Schema::create('penukaran_souvenirs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_rkm');
            $table->unsignedBigInteger('id_regist'); // Relasi ke peserta
            $table->unsignedBigInteger('id_souvenir_lama');
            $table->unsignedBigInteger('id_souvenir_baru');
            $table->dateTime('tanggal_tukar');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penukaran_souvenirs');
    }
};
