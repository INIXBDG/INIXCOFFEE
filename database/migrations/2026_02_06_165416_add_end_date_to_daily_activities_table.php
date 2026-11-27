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
        if (Schema::hasTable('daily_activities')) {
            Schema::table('daily_activities', function (Blueprint $table) {
                if (!Schema::hasColumn('daily_activities', 'end_date')) {
                    $table->date('end_date')->nullable()->after('start_date');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_activities', function (Blueprint $table) {
            //
        });
    }
};
