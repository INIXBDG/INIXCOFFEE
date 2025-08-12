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
        Schema::create('tipe_kategori_tabels', function (Blueprint $table) {
            $table->id();
            $table->integer('id_kategori');
            $table->string('ket_tipe')->nullable();
            $table->string('nilai_ket_tipe')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipe_kategori_tabels');
    }
};
