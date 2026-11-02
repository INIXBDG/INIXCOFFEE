<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_password_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_nama')->nullable();
            $table->string('user_jabatan')->nullable();
            $table->string('aksi', 60);
            $table->unsignedBigInteger('data_id')->nullable();
            $table->string('data_judul')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('device')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('keterangan', 500)->nullable();
            $table->string('sumber', 10)->default('sistem');
            $table->dateTime('waktu');

            $table->index('waktu');
            $table->index('aksi');
            $table->index('user_id');
            $table->index('ip_address');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_password_logs');
    }
};