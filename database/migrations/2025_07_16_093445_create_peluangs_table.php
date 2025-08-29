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
            $table->string('id_sales');
            $table->integer('id_rkm')->nullable();
            $table->integer('materi');
            $table->text('catatan')->nullable();
            $table->decimal('harga', 15,2);
            $table->decimal('netsales', 15,2)->nullable();
            $table->date('periode_mulai')->nullable();
            $table->date('periode_selesai')->nullable();
            $table->integer('pax');
            $table->decimal('final', 15,2)->nullable();
            $table->text('desc_lost')->nullable();
            $table->date('biru')->nullable();
            $table->date('merah')->nullable();
            $table->date('lost')->nullable();
            $table->enum('tahap', ['lead', 'hitam', 'biru', 'merah','lost'])->default('hitam');
            $table->boolean('tentatif')->default(0);
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
