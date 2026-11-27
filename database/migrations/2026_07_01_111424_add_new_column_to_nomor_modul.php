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
        if (Schema::hasTable('nomor_moduls')) {
            Schema::table('nomor_moduls', function (Blueprint $table) {
                if (!Schema::hasColumn('nomor_moduls', 'delay')) {
                    $table->enum('delay', ['client', 'admin'])->nullable()->after('status');
                }
                if (!Schema::hasColumn('nomor_moduls', 'keterangan')) {
                    $table->string('keterangan')->nullable()->after('delay');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nomor_moduls', function (Blueprint $table) {
            $table->dropColumn('delay');
            $table->dropColumn('keterangan');
        });
    }
};
