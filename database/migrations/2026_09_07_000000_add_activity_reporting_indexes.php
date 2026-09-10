<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aktivitas', function (Blueprint $table) {
            $table->index(
                [DB::raw('id_sales(50)'), 'waktu_aktivitas'],
                'idx_aktivitas_sales_waktu'
            );

            $table->index(
                [DB::raw('id_sales(50)'), 'aktivitas'],
                'idx_aktivitas_sales_aktivitas'
            );

            $table->index('created_at', 'idx_aktivitas_created');
            $table->index('id_peserta', 'idx_aktivitas_peserta');
        });

        Schema::table('target_activities', function (Blueprint $table) {
            $table->index('id_sales', 'idx_target_activities_sales');
        });
    }

    public function down(): void
    {
        Schema::table('target_activities', function (Blueprint $table) {
            $table->dropIndex('idx_target_activities_sales');
        });

        Schema::table('aktivitas', function (Blueprint $table) {
            $table->dropIndex('idx_aktivitas_sales_waktu');
            $table->dropIndex('idx_aktivitas_sales_aktivitas');
            $table->dropIndex('idx_aktivitas_created');
            $table->dropIndex('idx_aktivitas_peserta');
        });
    }
};
