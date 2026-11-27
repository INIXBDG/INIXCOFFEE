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
                if (Schema::hasColumn('nomor_moduls', 'tanggal_tenggat')) {
                    $table->dateTime('tanggal_tenggat')->nullable()->change();
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
            $table->date('tanggal_tenggat')->nullable()->change();
        });
    }
};
