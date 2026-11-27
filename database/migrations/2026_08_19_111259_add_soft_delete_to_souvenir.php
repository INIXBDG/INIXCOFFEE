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
        if (Schema::hasTable('souvenirs')) {
            Schema::table('souvenirs', function (Blueprint $table) {
                if (!Schema::hasColumn('souvenirs', 'deleted_at')) {
                    $table->softDeletes();
                }
                if (!Schema::hasColumn('souvenirs', 'deleted_by')) {
                    $table->string('deleted_by')->nullable()->after('deleted_at');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('souvenirs', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn('deleted_by');
        });
    }
};
