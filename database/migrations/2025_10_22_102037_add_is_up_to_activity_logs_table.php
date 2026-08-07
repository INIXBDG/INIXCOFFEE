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
        if (!Schema::hasTable('activity_logs')) {
            return;
        }

        $columnsToAdd = [];

        if (!Schema::hasColumn('activity_logs', 'is_up')) {
            $columnsToAdd[] = 'is_up';
        }

        if (!Schema::hasColumn('activity_logs', 'response_time_ms')) {
            $columnsToAdd[] = 'response_time_ms';
        }

        if (!Schema::hasColumn('activity_logs', 'checked_at')) {
            $columnsToAdd[] = 'checked_at';
        }

        if (empty($columnsToAdd)) {
            return;
        }

        Schema::table('activity_logs', function (Blueprint $table) use ($columnsToAdd) {
            if (in_array('is_up', $columnsToAdd, true)) {
                $table->boolean('is_up')->nullable();
            }

            if (in_array('response_time_ms', $columnsToAdd, true)) {
                $table->integer('response_time_ms')->nullable();
            }

            if (in_array('checked_at', $columnsToAdd, true)) {
                $table->timestamp('checked_at')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('activity_logs')) {
            return;
        }

        $columnsToDrop = array_filter(
            ['is_up', 'response_time_ms', 'checked_at'],
            function ($column) {
                return Schema::hasColumn('activity_logs', $column);
            }
        );

        if (empty($columnsToDrop)) {
            return;
        }

        Schema::table('activity_logs', function (Blueprint $table) use ($columnsToDrop) {
            $table->dropColumn(array_values($columnsToDrop));
        });
    }
};