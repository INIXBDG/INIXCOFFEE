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
        if (Schema::hasTable('activity_logs')) {
            Schema::table('activity_logs', function (Blueprint $table) {
                if (!Schema::hasColumn('activity_logs', 'is_up')) {
                    $table->boolean('is_up')->nullable();
                }
                if (!Schema::hasColumn('activity_logs', 'response_time_ms')) {
                    $table->integer('response_time_ms')->nullable();
                }
                if (!Schema::hasColumn('activity_logs', 'checked_at')) {
                    $table->timestamp('checked_at')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('activity_logs')) {
            $cols = array_values(array_filter(
                ['is_up', 'response_time_ms', 'checked_at'],
                fn (string $col) => Schema::hasColumn('activity_logs', $col)
            ));

            if (!empty($cols)) {
                Schema::table('activity_logs', function (Blueprint $table) use ($cols) {
                    $table->dropColumn($cols);
                });
            }
        }
    }
};
