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
        if (Schema::hasTable('outstandings')) {
            Schema::table('outstandings', function (Blueprint $table) {
                if (!Schema::hasColumn('outstandings', 'deleted_at')) {
                    $table->softDeletes();
                }
                if (!Schema::hasColumn('outstandings', 'deleted_by')) {
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
        Schema::table('outstandings', function (Blueprint $table) {
            //
        });
    }
};
