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
        Schema::create('pengajuan_subs', function (Blueprint $table) {
            $table->id();
            $table->string('kode_karyawan');
            $table->unsignedBigInteger('id_subs')->nullable();
            $table->unsignedBigInteger('id_rkm')->nullable();
            $table->unsignedBigInteger('id_tracking')->nullable();
            $table->string('invoice')->nullable();
            $table->string('jenis_transaksi')->nullable();
            $table->text('subs_snapshot')->nullable(); // stored as JSON
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajuan_subs');
    }
};
