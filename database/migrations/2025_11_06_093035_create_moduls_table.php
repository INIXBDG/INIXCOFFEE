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
            
            $table->text('nomor');
            $table->enum('tipe', ['Regular', 'Authorize']);

            $table->string('kode_materi');
            $table->string('nama_materi');

            $table->date('awal_training');
            $table->date('akhir_training');

            $table->string('nama_peserta')->nullable();
            $table->string('kontak_peserta')->nullable(); // ← diubah jadi kontak_peserta

            $table->integer('jumlah')->default(1);
            $table->decimal('harga_satuan', 12, 2)->default(0); // ← lebih jelas
            $table->decimal('subtotal', 12, 2)->default(0);

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
