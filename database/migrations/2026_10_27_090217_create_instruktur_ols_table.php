<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('instruktur_ols')) {
            return;
        }

        Schema::create('instruktur_ols', function (Blueprint $table) {
            $table->id();
            $table->string('kode_karyawan', 20)->nullable()->index();
            $table->string('nama');
            $table->string('core');
            $table->string('no_hp', 20);
            $table->text('keterangan')->nullable();
            $table->string('cv_path')->nullable();
            $table->string('ktp_path')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instruktur_ols');
    }
};