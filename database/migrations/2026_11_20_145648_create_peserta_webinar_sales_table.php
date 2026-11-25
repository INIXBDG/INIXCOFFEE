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
        Schema::create('peserta_webinar_sales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('year_mapping_id');
            $table->text('email_peserta');
            $table->text('nama_peserta');
            $table->text('no_hp')->nullable();
            $table->text('id_perusahaan')->nullable();
            $table->text('id_sales')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peserta_webinar_sales');
    }
};
