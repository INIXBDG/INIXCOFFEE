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
        if (!Schema::hasColumn('karyawans', 'email')) {
            Schema::table('karyawans', function (Blueprint $table) {
                $table->text('email')->nullable();
            });
        }

        if (!Schema::hasColumn('karyawans', 'whatsapp')) {
            Schema::table('karyawans', function (Blueprint $table) {
                $table->string('whatsapp')->nullable();
            });
        }

        if (!Schema::hasColumn('karyawans', 'telepon')) {
            Schema::table('karyawans', function (Blueprint $table) {
                $table->string('telepon')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('karyawans', function (Blueprint $table) {
            $table->dropColumn(['email', 'whatsapp', 'telepon']);
        });
    }
};
