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
        if (Schema::hasTable('approval_pendapatans')) {
            Schema::table('approval_pendapatans', function (Blueprint $table) {
                if (!Schema::hasColumn('approval_pendapatans', 'biaya_lain_lain')) {
                    $table->bigInteger('biaya_lain_lain')->nullable()->after('oleh_oleh');
                }
                if (!Schema::hasColumn('approval_pendapatans', 'pengurangan_pph')) {
                    $table->bigInteger('pengurangan_pph')->nullable()->after('biaya_lain_lain');
                }
            });
        }

        if (Schema::hasTable('approval_pendapatan_sales')) {
            Schema::table('approval_pendapatan_sales', function (Blueprint $table) {
                if (!Schema::hasColumn('approval_pendapatan_sales', 'biaya_lain_lain')) {
                    $table->bigInteger('biaya_lain_lain')->nullable()->after('oleh_oleh');
                }
                if (!Schema::hasColumn('approval_pendapatan_sales', 'exam')) {
                    $table->bigInteger('exam')->nullable()->after('biaya_lain_lain');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approval_pendapatans', function (Blueprint $table) {
            $table->dropColumn('biaya_lain_lain');
            $table->dropColumn('pengurangan_pph');
        });
    }
};
