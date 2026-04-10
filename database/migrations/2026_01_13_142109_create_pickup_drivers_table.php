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
        Schema::create('pickup_drivers', function (Blueprint $table) {
            $table->id();
            $table->integer('id_karyawan');
            $table->integer('id_pembuat');
            $table->integer('status_apply')->nullable();
            $table->time('waktu_kepulangan')->nullable();
            $table->string('status_driver')->nullable();
            $table->string('kendaraan')->nullable();
            $table->integer('budget')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pickup_drivers');
    }
};
