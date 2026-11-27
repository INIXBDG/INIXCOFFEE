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
        if (Schema::hasTable('perhitungan_tunjangan_h_r_s')) {
            Schema::table('perhitungan_tunjangan_h_r_s', function (Blueprint $table) {
                if (!Schema::hasColumn('perhitungan_tunjangan_h_r_s', 'biaya_tanggungan')) {
                    $table->string('biaya_tanggungan')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('perhitungan_tunjangan_h_r', function (Blueprint $table) {
            //
        });
    }
};
