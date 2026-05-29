<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('peluang_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_peluang');
            $table->string('tahap_sebelumnya');
            $table->string('tahap_baru');
            $table->double('harga_sebelumnya');
            $table->double('harga_baru');
            $table->integer('pax_sebelumnya');
            $table->integer('pax_baru');
            $table->date('periode_mulai_sebelumnya')->nullable();
            $table->date('periode_mulai_baru')->nullable();
            $table->date('periode_selesai_sebelumnya')->nullable();
            $table->date('periode_selesai_baru')->nullable();
            $table->string('keterangan')->nullable();
            $table->timestamps();

            $table->foreign('id_peluang')->references('id')->on('peluangs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peluang_histories');
    }
};
