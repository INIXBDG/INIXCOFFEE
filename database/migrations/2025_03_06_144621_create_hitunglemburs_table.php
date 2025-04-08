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
        Schema::create('hitunglemburs', function (Blueprint $table) {
            $table->id();
            $table->string('id_lembur')->nullable();
            $table->decimal('nilai_lembur', 15, 2)->nullable();
            $table->string('approval_gm')->nullable();
            $table->string('alasan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hitunglemburs');
    }
};
