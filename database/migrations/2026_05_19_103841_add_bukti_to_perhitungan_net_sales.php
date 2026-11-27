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
        if (Schema::hasTable('perhitungan_net_sales')) {
            Schema::table('perhitungan_net_sales', function (Blueprint $table) {
                if (!Schema::hasColumn('perhitungan_net_sales', 'bukti')) {
                    $table->string('bukti')->nullable()->after('tipe_pembayaran');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('perhitungan_net_sales', function (Blueprint $table) {
            $table->dropColumn('bukti');
        });
    }
};
