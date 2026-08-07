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
        if (!Schema::hasTable('daily_activities')) {
            return;
        }

        if (Schema::hasColumn('daily_activities', 'end_date')) {
            return;
        }

        $hasStartDate = Schema::hasColumn('daily_activities', 'start_date');

        Schema::table('daily_activities', function (Blueprint $table) use ($hasStartDate) {
            if ($hasStartDate) {
                $table->date('end_date')->nullable()->after('start_date');
            } else {
                $table->date('end_date')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('daily_activities')) {
            return;
        }

        if (!Schema::hasColumn('daily_activities', 'end_date')) {
            return;
        }

        Schema::table('daily_activities', function (Blueprint $table) {
            $table->dropColumn('end_date');
        });
    }
};