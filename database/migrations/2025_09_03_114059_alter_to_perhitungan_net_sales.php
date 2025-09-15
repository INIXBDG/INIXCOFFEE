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
        Schema::table('perhitungan_net_sales', function (Blueprint $table) {
            $table->integer('id_peserta')->nullable()->after('id_rkm');
            $table->decimal('cashback', 15)->nullable()->after('fresh_money');
            $table->decimal('diskon', 15)->nullable()->after('cashback');
            $table->text('desc')->nullable()->after('tipe_pembayaran');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('perhitungan_net_sales', function (Blueprint $table) {
        });
    }
};
