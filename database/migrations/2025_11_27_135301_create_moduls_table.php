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
        Schema::create('moduls', function (Blueprint $table) {
            $table->id();
            $table->text('no_modul');
            $table->text('kode_materi');
            $table->text('nama_materi');
            $table->date('awal_training');
            $table->date('akhir_training');
            $table->integer('jumlah');
            $table->decimal('harga_satuan', 15,2);
            $table->decimal('total', 15,2);
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('moduls');
    }
};
