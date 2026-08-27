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
        Schema::create('kondisi_tools', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_alat')->constrained('ob_tools')->cascadeOnDelete();
            $table->string('kondisi');
            $table->date('tanggal_pemeriksaan');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kondisi_tools');
    }
};
