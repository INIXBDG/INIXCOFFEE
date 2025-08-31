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
        Schema::create('aktivitas', function (Blueprint $table) {
            $table->id();
            $table->text('id_sales');
            $table->integer('id_contact');
            $table->integer('id_peserta')->nullable();
            $table->integer('id_peluang')->nullable();
            $table->enum('aktivitas', ['Call', 'Email', 'Visit', 'Meet']);
            $table->text('subject');
            $table->text('deskripsi')->nullable();
            $table->date('waktu_aktivitas');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aktivitas');
    }
};
