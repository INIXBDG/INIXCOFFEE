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
                if (!Schema::hasColumn('nilaifeedbacks', 'M')) {
                    $table->string('M')->nullable();
                }
                if (!Schema::hasColumn('nilaifeedbacks', 'TextM')) {
                    $table->text('TextM')->nullable();
                }
                if (!Schema::hasColumn('nilaifeedbacks', 'P')) {
                    $table->string('P')->nullable();
                }
                if (!Schema::hasColumn('nilaifeedbacks', 'TextP')) {
                    $table->text('TextP')->nullable();
                }
                if (!Schema::hasColumn('nilaifeedbacks', 'F')) {
                    $table->string('F')->nullable();
                }
                if (!Schema::hasColumn('nilaifeedbacks', 'TextF')) {
                    $table->text('TextF')->nullable();
                }
                if (!Schema::hasColumn('nilaifeedbacks', 'I')) {
                    $table->string('I')->nullable();
                }
                if (!Schema::hasColumn('nilaifeedbacks', 'TextI')) {
                    $table->text('TextI')->nullable();
                }
                if (!Schema::hasColumn('nilaifeedbacks', 'IB')) {
                    $table->string('IB')->nullable();
                }
                if (!Schema::hasColumn('nilaifeedbacks', 'TextIB')) {
                    $table->text('TextIB')->nullable();
                }
                if (!Schema::hasColumn('nilaifeedbacks', 'IAS')) {
                    $table->string('IAS')->nullable();
                }
                if (!Schema::hasColumn('nilaifeedbacks', 'TextIAS')) {
                    $table->text('TextIAS')->nullable();
                }
                if (!Schema::hasColumn('nilaifeedbacks', 'S')) {
                    $table->string('S')->nullable();
                }
                if (!Schema::hasColumn('nilaifeedbacks', 'TextS')) {
                    $table->text('TextS')->nullable();
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
