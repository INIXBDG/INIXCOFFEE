<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('perhitungan_net_sales')) {
            Schema::table('perhitungan_net_sales', function (Blueprint $table) {
                if (!Schema::hasColumn('perhitungan_net_sales', 'oleh_oleh')) {
                    $table->decimal('oleh_oleh', 15, 2)->nullable()->after('souvenir');
                }
                if (!Schema::hasColumn('perhitungan_net_sales', 'biaya_lain_lain')) {
                    $table->decimal('biaya_lain_lain', 15, 2)->nullable()->after('oleh_oleh');
                }
                if (!Schema::hasColumn('perhitungan_net_sales', 'keterangan_biaya_lain')) {
                    $table->text('keterangan_biaya_lain')->nullable()->after('biaya_lain_lain');
                }
                if (!Schema::hasColumn('perhitungan_net_sales', 'exam')) {
                    $table->decimal('exam', 15, 2)->nullable()->after('sewa_laptop');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('perhitungan_net_sales', function (Blueprint $table) {
            $table->dropColumn([
                'oleh_oleh',
                'biaya_lain_lain',
                'keterangan_biaya_lain',
                'exam',
            ]);
        });
    }
};