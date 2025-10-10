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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('kode_karyawan');
            $table->string('nama_subs');
            $table->string('merk');
            $table->text('desc');
            $table->string('subs_url')->nullable();
            $table->string('access_code')->nullable();
            $table->string('mata_uang')->nullable();
            $table->decimal('harga', 15,2)->nullable();
            $table->decimal('kurs', 15, 2)->nullable();
            $table->decimal('harga_rupiah', 15, 2)->nullable();
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->enum('status', ['active', 'expired', 'pending'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
