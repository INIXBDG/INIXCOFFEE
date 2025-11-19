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
        Schema::create('detail_caterings', function (Blueprint $table) {
            $table->id();
            $table->integer('id_catering');
            $table->integer('id_vendor');
            $table->string('nama_makanan');
            $table->integer('jumlah');
            $table->decimal('harga');
            $table->enum('tipe_detail', ['Coffee Break', 'Makan Siang']);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_caterings');
    }
};
