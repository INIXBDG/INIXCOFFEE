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
        Schema::create('jenis_tunjangans', function (Blueprint $table) {
            $table->id();
            $table->string('nama_tunjangan');  
            $table->decimal('nilai', 15, 2);
            $table->enum('tipe', ['Tunjangan', 'Potongan']);
            $table->enum('hitung', ['Perhari', 'Perbulan']);
            $table->string('divisi');  
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jenis_tunjangans');
    }
};
