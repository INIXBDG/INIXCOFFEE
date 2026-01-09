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
        Schema::create('rincian_kegiatans', function (Blueprint $table) {
            $table->id();
            $table->integer('id_kegiatan');
            $table->text('hal');
            $table->text('rincian')->nullable();
            $table->integer('qty');
            $table->decimal('harga_satuan', 15,2);
            $table->decimal('total', 15,2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rincian_kegiatans');
    }
};
