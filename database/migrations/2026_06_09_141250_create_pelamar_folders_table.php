<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pelamar_folders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('folder_id');
            $table->unsignedBigInteger('pelamar_id');
            $table->integer('rating')->nullable();
            $table->text('catatan')->nullable();
            $table->string('file_penilaian')->nullable();
            $table->unsignedBigInteger('dinilai_oleh')->nullable();
            $table->timestamp('tanggal_dinilai')->nullable();
            $table->boolean('is_archived')->default(false);
            $table->timestamps();

            $table->foreign('folder_id')->references('id')->on('folders')->onDelete('cascade');
            $table->foreign('pelamar_id')->references('id')->on('pelamars')->onDelete('cascade');
            $table->foreign('dinilai_oleh')->references('id')->on('users')->onDelete('set null');
            
            $table->unique(['pelamar_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pelamar_folders');
    }
};