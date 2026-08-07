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
        if (!Schema::hasColumn('activity_logs', 'is_up')) {
            Schema::table('activity_logs', function (Blueprint $table) {
                $table->boolean('is_up')->nullable();
            });
        }

        if (!Schema::hasColumn('activity_logs', 'response_time_ms')) {
            Schema::table('activity_logs', function (Blueprint $table) {
                $table->integer('response_time_ms')->nullable();
            });
        }

        if (!Schema::hasColumn('activity_logs', 'checked_at')) {
            Schema::table('activity_logs', function (Blueprint $table) {
                $table->timestamp('checked_at')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('activity_logs', 'is_up')) {
            Schema::table('activity_logs', function (Blueprint $table) {
                $table->dropColumn('is_up');
            });
        }

        if (Schema::hasColumn('activity_logs', 'response_time_ms')) {
            Schema::table('activity_logs', function (Blueprint $table) {
                $table->dropColumn('response_time_ms');
            });
        }

        if (Schema::hasColumn('activity_logs', 'checked_at')) {
            Schema::table('activity_logs', function (Blueprint $table) {
                $table->dropColumn('checked_at');
            });
        }
    }
};
