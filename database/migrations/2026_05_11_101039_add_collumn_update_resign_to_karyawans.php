<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('karyawans')) {
            Schema::table('karyawans', function (Blueprint $table) {
                if (!Schema::hasColumn('karyawans', 'resigned_at')) {
                    $table->dateTime('resigned_at')->nullable();
                }
                if (!Schema::hasColumn('karyawans', 'alasan_resign')) {
                    $table->text('alasan_resign')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('karyawans', function (Blueprint $table) {
            //
        });
    }
};
