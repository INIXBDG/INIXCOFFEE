<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tracking_tagihan_perusahaans') && Schema::hasColumn('tracking_tagihan_perusahaans', 'status')) {
            DB::statement("ALTER TABLE tracking_tagihan_perusahaans MODIFY status ENUM('pending','proses','selesai','telat','on_time') NULL DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tracking_tagihan_perusahaans') && Schema::hasColumn('tracking_tagihan_perusahaans', 'status')) {
            DB::table('tracking_tagihan_perusahaans')->where('status', 'on_time')->update(['status' => 'selesai']);

            DB::statement("ALTER TABLE tracking_tagihan_perusahaans MODIFY status ENUM('pending','proses','selesai','telat') NULL DEFAULT 'pending'");
        }
    }
};