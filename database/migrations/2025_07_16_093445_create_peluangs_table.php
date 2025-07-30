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
        Schema::create('peluangs', function (Blueprint $table) {
            $table->id();
            $table->integer('id_contact');
            $table->text('id_sales');
            $table->text('materi');
            $table->text('catatan')->nullable();
            $table->decimal('harga', 15,2);
            $table->decimal('netsales', 15,2);
            $table->date('periode_mulai');
            $table->date('periode_selesai');
            $table->integer('pax');
            $table->decimal('final', 15,2)->nullable();
            $table->date('biru')->nullable();
            $table->date('merah')->nullable();
            $table->enum('tahap', ['lead', 'hitam', 'biru', 'merah'])->default('hitam');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peluangs');
    }
};
