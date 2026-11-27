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
        if (Schema::hasTable('karyawans')) {
            Schema::table('karyawans', function (Blueprint $table) {
                if (!Schema::hasColumn('karyawans', 'pendidikan_terakhir')) {
                    $table->string('pendidikan_terakhir')->nullable()->after('tanggal_lahir');
                }
                if (!Schema::hasColumn('karyawans', 'kejuruan')) {
                    $table->string('kejuruan')->nullable()->after('pendidikan_terakhir');
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
