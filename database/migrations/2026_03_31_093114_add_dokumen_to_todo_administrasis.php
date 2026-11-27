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
        if (Schema::hasTable('todo_administrasis')) {
            Schema::table('todo_administrasis', function (Blueprint $table) {
                if (Schema::hasColumn('todo_administrasis', 'solusi')) {
                    $table->longText('solusi')->nullable()->change();
                }
                if (Schema::hasColumn('todo_administrasis', 'catatan')) {
                    $table->longText('catatan')->nullable()->change();
                }
                if (!Schema::hasColumn('todo_administrasis', 'tanggal_selesai')) {
                    $table->date('tanggal_selesai')->nullable();
                }
                if (!Schema::hasColumn('todo_administrasis', 'dokumen')) {
                    $table->string('dokumen')->nullable();
                }
            });
        }
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
