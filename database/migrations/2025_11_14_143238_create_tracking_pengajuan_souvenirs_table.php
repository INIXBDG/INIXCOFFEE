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
        Schema::create('tracking_pengajuan_souvenirs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_pengajuan_souvenir');
            $table->string('tracking');
            $table->date('tanggal');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracking_pengajuan_souvenirs');
    }
};
