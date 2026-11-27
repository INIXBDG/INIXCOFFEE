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
        if (Schema::hasTable('targets')) {
            Schema::table('targets', function (Blueprint $table) {
                if (!Schema::hasColumn('targets', 'target_project')) {
                    $table->string('target_project')->nullable()->after('target');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('targets', function (Blueprint $table) {
            $table->dropColumn('target_project');
        });
    }
};
