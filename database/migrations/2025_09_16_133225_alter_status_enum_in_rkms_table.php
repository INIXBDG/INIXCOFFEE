<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('r_k_m_s') && Schema::hasColumn('r_k_m_s', 'status')) {
            DB::statement("ALTER TABLE r_k_m_s MODIFY status ENUM('0','1','2','3') NOT NULL DEFAULT '0'");
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('r_k_m_s') && Schema::hasColumn('r_k_m_s', 'status')) {
            DB::statement("ALTER TABLE r_k_m_s MODIFY status ENUM('0','1','2') NOT NULL DEFAULT '0'");
        }
    }
};
