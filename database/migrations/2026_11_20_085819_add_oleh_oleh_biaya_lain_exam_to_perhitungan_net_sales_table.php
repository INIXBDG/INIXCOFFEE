<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('perhitungan_net_sales', function (Blueprint $table) {
            $table->decimal('oleh_oleh', 15, 2)->nullable()->after('souvenir');
            $table->decimal('biaya_lain_lain', 15, 2)->nullable()->after('oleh_oleh');
            $table->text('keterangan_biaya_lain')->nullable()->after('biaya_lain_lain');
            $table->decimal('exam', 15, 2)->nullable()->after('sewa_laptop');
        });
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