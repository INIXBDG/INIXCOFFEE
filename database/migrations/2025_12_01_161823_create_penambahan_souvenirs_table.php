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
        Schema::create('penambahan_souvenirs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_rkm');
            $table->unsignedBigInteger('id_karyawan');
            $table->unsignedBigInteger('id_souvenir');
            $table->string('nama');
            $table->string('jabatan');
            $table->integer('qty');
            $table->date('tanggal');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penambahan_souvenirs');
    }
};
