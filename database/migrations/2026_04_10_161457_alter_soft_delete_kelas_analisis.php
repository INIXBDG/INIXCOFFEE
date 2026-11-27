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
        if (Schema::hasTable('kelasanalises')) {
            Schema::table('kelasanalises', function (Blueprint $table) {
                if (!Schema::hasColumn('kelasanalises', 'deleted_at')) {
                    $table->softDeletes();
                }
                if (!Schema::hasColumn('kelasanalises', 'deleted_by')) {
                    $table->string('deleted_by')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kelasanalises', function (Blueprint $table) {
            //
        });
    }
};
