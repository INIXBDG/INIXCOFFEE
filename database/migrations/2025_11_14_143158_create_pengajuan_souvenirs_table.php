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
        Schema::create('pengajuan_souvenirs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_vendor');
            $table->unsignedBigInteger('id_souvenir');
            $table->string('pax');
            $table->decimal('harga_satuan', 15);
            $table->decimal('harga_total', 15);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajuan_souvenirs');
    }
};
