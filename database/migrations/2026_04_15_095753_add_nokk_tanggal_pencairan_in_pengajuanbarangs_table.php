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
        if (Schema::hasTable('pengajuanbarangs')) {
            Schema::table('pengajuanbarangs', function (Blueprint $table) {
                if (!Schema::hasColumn('pengajuanbarangs', 'no_kk')) {
                    $table->string('no_kk')->nullable();
                }
                if (!Schema::hasColumn('pengajuanbarangs', 'tanggal_pencairan')) {
                    $table->date('tanggal_pencairan')->nullable();
                }
                if (!Schema::hasColumn('pengajuanbarangs', 'tanggal_terima_finance')) {
                    $table->date('tanggal_terima_finance')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengajuanbarangs', function (Blueprint $table) {
            //
        });
    }
};
