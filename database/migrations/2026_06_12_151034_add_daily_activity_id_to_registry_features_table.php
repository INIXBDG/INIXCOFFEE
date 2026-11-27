<?php

use App\Support\MigrationHelper;
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
        if (Schema::hasTable('registry_features')) {
            if (!Schema::hasColumn('registry_features', 'daily_activity_id')) {
                Schema::table('registry_features', function (Blueprint $table) {
                    $col = $table->unsignedBigInteger('daily_activity_id')->nullable();
                    if (Schema::hasColumn('registry_features', 'ticket_id')) {
                        $col->after('ticket_id');
                    }
                });
            }

            if (
                Schema::hasColumn('registry_features', 'daily_activity_id')
                && Schema::hasTable('daily_activities')
                && !MigrationHelper::foreignKeyExistsOnColumn('registry_features', 'daily_activity_id', 'daily_activities')
            ) {
                Schema::table('registry_features', function (Blueprint $table) {
                    // Penambahan Foreign Key Constraint
                    $table->foreign('daily_activity_id')
                          ->references('id')
                          ->on('daily_activities')
                          ->onDelete('set null');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('registry_features')) {
            MigrationHelper::dropForeignIfExists('registry_features', ['daily_activity_id']);

            if (Schema::hasColumn('registry_features', 'daily_activity_id')) {
                Schema::table('registry_features', function (Blueprint $table) {
                    $table->dropColumn('daily_activity_id');
                });
            }
        }
    }
};
