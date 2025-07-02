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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->text('idbarang'); // Barang yang diservis
            $table->date('tanggal_service'); // Tanggal service
            $table->text('deskripsi'); // Deskripsi service, e.g., "Service laptop sales Bu Hera"
            $table->decimal('harga', 15, 2)->nullable(); // Biaya service
            $table->text('user'); // Pengguna yang mencatat service
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
