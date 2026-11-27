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
                if (Schema::hasColumn('nilaifeedbacks', 'U1')) {
                    $table->text('U1')->change();
                }
                if (Schema::hasColumn('nilaifeedbacks', 'U2')) {
                    $table->text('U2')->change();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nilaifeedbacks', function (Blueprint $table) {
            //
        });
    }
};
