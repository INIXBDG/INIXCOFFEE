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
        if (!Schema::hasColumn('project_administrations', 'assignee_id')) {
            Schema::table('project_administrations', function (Blueprint $table) {
                $table->string('assignee_id')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_administrations', function (Blueprint $table) {
            $table->dropColumn('assignee_id');
        });
    }
};
