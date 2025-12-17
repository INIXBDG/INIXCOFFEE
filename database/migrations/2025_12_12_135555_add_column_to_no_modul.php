<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nomor_moduls', function (Blueprint $table) {
            $table->text('note_modul')->nullable();
            $table->text('note_peserta')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('nomor_moduls', function (Blueprint $table) {
        });
    }
};
