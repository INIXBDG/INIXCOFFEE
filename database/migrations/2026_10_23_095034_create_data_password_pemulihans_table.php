<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_password_pemulihan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('user_nama');
            $table->string('user_jabatan')->nullable();
            $table->string('status', 20)->default('gagal')->index();
            $table->string('jawaban_nama')->nullable();
            $table->string('jawaban_judul')->nullable();
            $table->text('alasan')->nullable();
            $table->boolean('konfirmasi')->default(false);
            $table->json('hasil')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('device')->nullable();
            $table->timestamp('diverifikasi_at')->nullable();
            $table->timestamp('selesai_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_password_pemulihan');
    }
};