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
        Schema::create('koordinasi_office_boys', function (Blueprint $table) {
            $table->id();
            $table->string('nama_tugas');
            $table->integer('karyawan');
            $table->string('status')->default('Dikerjakan');
            $table->dateTime('deadline');
            $table->text('catatan')->nullable();
            $table->integer('created_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('koordinasi_office_boys');
    }
};
