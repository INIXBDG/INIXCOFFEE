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
        if (Schema::hasTable('lemburs')) {
            Schema::table('lemburs', function (Blueprint $table) {
                if (!Schema::hasColumn('lemburs', 'foto_masuk')) {
                    $table->string('foto_masuk')->nullable();
                }
                if (!Schema::hasColumn('lemburs', 'foto_selesai')) {
                    $table->string('foto_selesai')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lemburs', function (Blueprint $table) {
            //
        });
    }
};
