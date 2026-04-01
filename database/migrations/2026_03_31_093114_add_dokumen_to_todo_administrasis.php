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
        Schema::table('todo_administrasis', function (Blueprint $table) {
            $table->longText('solusi')->nullable()->change();
            $table->longText('catatan')->nullable()->change();
            $table->date('tanggal_selesai')->nullable();
            $table->string('dokumen')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('todo_administrasis', function (Blueprint $table) {
            $table->string('solusi')->nullable()->change();
            $table->string('catatan')->nullable()->change();
        });
    }
};
