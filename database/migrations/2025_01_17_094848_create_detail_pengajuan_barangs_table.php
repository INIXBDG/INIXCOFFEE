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
        Schema::create('detail_pengajuan_barangs', function (Blueprint $table) {
            $table->id();
            $table->string('id_pengajuan_barang')->nullable();
            $table->string('nama_barang')->nullable();
            $table->string('qty')->nullable();
            $table->decimal('harga', 15,2)->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_pengajuan_barangs');
    }
};
