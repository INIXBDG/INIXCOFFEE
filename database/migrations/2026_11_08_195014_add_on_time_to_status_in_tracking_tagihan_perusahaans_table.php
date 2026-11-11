<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE tracking_tagihan_perusahaans MODIFY status ENUM('pending','proses','selesai','telat','on_time') NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::table('tracking_tagihan_perusahaans')->where('status', 'on_time')->update(['status' => 'selesai']);

        DB::statement("ALTER TABLE tracking_tagihan_perusahaans MODIFY status ENUM('pending','proses','selesai','telat') NULL DEFAULT 'pending'");
    }
};