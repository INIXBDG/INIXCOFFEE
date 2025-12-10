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
        Schema::create('detail_pengajuan_souvenirs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_pengajuan_souvenir');
            $table->unsignedBigInteger('id_souvenir');
            $table->integer('pax');
            $table->decimal('harga_satuan', 15, 2);
            $table->decimal('harga_total', 15, 2);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_pengajuan_souvenirs');
    }
};
