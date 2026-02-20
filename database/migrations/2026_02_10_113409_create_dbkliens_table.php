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
        Schema::create('dbkliens', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('jenis_kelamin')->nullable();
            $table->string('email')->nullable();
            $table->string('no_hp')->nullable();
            $table->string('alamat')->nullable();
            $table->string('nama_perusahaan')->nullable();
            $table->string('tanggal_lahir')->nullable();
            $table->string('nama_materi')->nullable();
            $table->string('sales_key')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dbkliens');
    }
};
