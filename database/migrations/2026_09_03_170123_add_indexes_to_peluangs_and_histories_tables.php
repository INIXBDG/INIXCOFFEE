<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi.
     */
    public function up(): void
    {
        Schema::table('peluangs', function (Blueprint $table) {
            $table->index('tahap', 'idx_peluangs_tahap');
            $table->index('id_sales', 'idx_peluangs_id_sales');
            $table->index('created_at', 'idx_peluangs_created_at');
        });

        Schema::table('peluang_histories', function (Blueprint $table) {
            $table->index('id_peluang', 'idx_peluang_histories_id_peluang');
        });
    }

    /**
     * Kembalikan migrasi.
     */
    public function down(): void
    {
        Schema::table('peluangs', function (Blueprint $table) {
            $table->dropIndex('idx_peluangs_tahap');
            $table->dropIndex('idx_peluangs_id_sales');
            $table->dropIndex('idx_peluangs_created_at');
        });

        Schema::table('peluang_histories', function (Blueprint $table) {
            $table->dropIndex('idx_peluang_histories_id_peluang');
        });
    }
};
