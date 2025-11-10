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
        Schema::create('registry_features', function (Blueprint $table) {
            $table->id();
            $table->string('tugas');
            $table->string('fitur')->nullable();
            $table->string('tipe');
            $table->string('pemilik');
            $table->unsignedBigInteger('pengerja_id')->nullable();
            $table->string('status');
            $table->dateTime('tanggal_mulai')->nullable();
            $table->dateTime('tanggal_akhir')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registry_features');
    }
};
