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
        if (Schema::hasTable('nilaifeedbacks')) {
            Schema::table('nilaifeedbacks', function (Blueprint $table) {
                if (!Schema::hasColumn('nilaifeedbacks', 's1')) {
                    $table->string('s1')->nullable();
                }
                if (!Schema::hasColumn('nilaifeedbacks', 's2')) {
                    $table->string('s2')->nullable();
                }
                if (!Schema::hasColumn('nilaifeedbacks', 's3')) {
                    $table->string('s3')->nullable();
                }
                if (!Schema::hasColumn('nilaifeedbacks', 's4')) {
                    $table->string('s4')->nullable();
                }
                if (!Schema::hasColumn('nilaifeedbacks', 's5')) {
                    $table->string('s5')->nullable();
                }
                if (!Schema::hasColumn('nilaifeedbacks', 's6')) {
                    $table->string('s6')->nullable();
                }
                if (!Schema::hasColumn('nilaifeedbacks', 's7')) {
                    $table->text('s7')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
