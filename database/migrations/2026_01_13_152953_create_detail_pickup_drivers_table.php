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
        Schema::create('detail_pickup_drivers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pickup_driver_id');
            $table->string('tipe');
            $table->string('lokasi');
            $table->date('tanggal_keberangkatan');
            $table->time('waktu_keberangkatan');
            $table->text('detail')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_pickup_drivers');
    }
};
