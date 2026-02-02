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
        Schema::create('komplain_pesertas', function (Blueprint $table) {
            $table->id();
            $table->text('komplain');
            $table->date('tanggal_selesai')->nullable();
            $table->enum('status', ['on progress', 'completed', 'delayed'])->default('on progress');
            $table->foreignId('nilaifeedback_id')->constrained()->cascadeOnDelete();
            $table->string('kategori_feedback');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('komplain_pesertas');
    }
};
