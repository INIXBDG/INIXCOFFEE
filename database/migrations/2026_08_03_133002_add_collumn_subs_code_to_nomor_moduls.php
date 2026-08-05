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
        Schema::table('nomor_moduls', function (Blueprint $table) {
            $table->boolean('status_subscode')->nullable()->after('note_peserta');
            $table->datetime('tanggal_subscode_masuk')->nullable()->after('status_subscode');
            $table->text('catatan')->nullable()->after('tanggal_subscode_masuk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nomor_moduls', function (Blueprint $table) {
            //
        });
    }
};
