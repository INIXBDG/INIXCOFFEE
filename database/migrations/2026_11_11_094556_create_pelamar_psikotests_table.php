<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pelamar_psikotests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pelamar_id');
            $table->string('kategori', 30);
            $table->string('hasil', 20)->nullable();
            $table->decimal('skor', 5, 2)->nullable();
            $table->json('skor_detail')->nullable();
            $table->date('tanggal_tes')->nullable();
            $table->text('catatan')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_nama')->nullable();
            $table->string('file_mime', 100)->nullable();
            $table->unsignedBigInteger('diinput_oleh')->nullable();
            $table->timestamps();

            $table->unique(['pelamar_id', 'kategori']);
            $table->foreign('pelamar_id')->references('id')->on('pelamars')->cascadeOnDelete();
        });

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE pelamars MODIFY tahap_rekrutmen VARCHAR(30) NOT NULL DEFAULT 'applied'");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pelamar_psikotests');
    }
};