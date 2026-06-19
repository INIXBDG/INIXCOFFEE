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
        Schema::table('registry_features', function (Blueprint $table) {
            $table->unsignedBigInteger('daily_activity_id')->nullable()->after('ticket_id');

            // Penambahan Foreign Key Constraint
            $table->foreign('daily_activity_id')
                  ->references('id')
                  ->on('daily_activities')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registry_features', function (Blueprint $table) {
            $table->dropForeign(['daily_activity_id']);
            $table->dropColumn('daily_activity_id');
        });
    }
};
