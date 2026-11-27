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
        if (Schema::hasTable('pesertas')) {
            Schema::table('pesertas', function (Blueprint $table) {
                if (!Schema::hasColumn('pesertas', 'checkregist')) {
                    $table->string('checkregist')->nullable();
                }

            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pesertas', function (Blueprint $table) {
            //
        });
    }
};
