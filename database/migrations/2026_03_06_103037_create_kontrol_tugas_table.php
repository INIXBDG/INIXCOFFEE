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
        Schema::create('kontrol_tugas', function (Blueprint $table) {
            $table->id();
            $table->integer('id_karyawan');
            $table->integer('id_DaftarTugas');
            $table->boolean('status');
            $table->date('Deadline_Date');
            $table->string('bukti')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kontrol_tugas');
    }
};
