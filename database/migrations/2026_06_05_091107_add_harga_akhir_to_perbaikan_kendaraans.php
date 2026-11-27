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
        if (Schema::hasTable('perbaikan_kendaraans')) {
            Schema::table('perbaikan_kendaraans', function (Blueprint $table) {
                if (!Schema::hasColumn('perbaikan_kendaraans', 'harga_akhir')) {
                    $table->integer('harga_akhir')->nullable()->after('estimasi');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('perbaikan_kendaraans', function (Blueprint $table) {
            $table->dropColumn('harga_akhir');
        });
    }
};
