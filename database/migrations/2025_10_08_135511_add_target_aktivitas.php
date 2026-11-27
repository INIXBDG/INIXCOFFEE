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
        if (Schema::hasTable('target_activities')) {
            Schema::table('target_activities', function (Blueprint $table) {
                if (!Schema::hasColumn('target_activities', 'DB')) {
                    $table->integer('DB');
                }
                if (!Schema::hasColumn('target_activities', 'PA')) {
                    $table->integer('PA');
                }
                if (!Schema::hasColumn('target_activities', 'PI')) {
                    $table->integer('PI');
                }
                if (!Schema::hasColumn('target_activities', 'Telemarketing')) {
                    $table->integer('Telemarketing');
                }
                if (!Schema::hasColumn('target_activities', 'FormM')) {
                    $table->integer('FormM');
                }
                if (!Schema::hasColumn('target_activities', 'FormK')) {
                    $table->integer('FormK');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('target_activities', function (Blueprint $table) {
            //
        });
    }
};
