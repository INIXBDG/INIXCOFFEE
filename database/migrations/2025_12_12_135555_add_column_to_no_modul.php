<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('nomor_moduls')) {
            Schema::table('nomor_moduls', function (Blueprint $table) {
                if (!Schema::hasColumn('nomor_moduls', 'note_modul')) {
                    $table->text('note_modul')->nullable();
                }
                if (!Schema::hasColumn('nomor_moduls', 'note_peserta')) {
                    $table->text('note_peserta')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('nomor_moduls', function (Blueprint $table) {
        });
    }
};
