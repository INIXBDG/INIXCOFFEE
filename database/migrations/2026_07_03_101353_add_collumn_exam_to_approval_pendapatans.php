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
        if (Schema::hasTable('approval_pendapatans')) {
            Schema::table('approval_pendapatans', function (Blueprint $table) {
                if (!Schema::hasColumn('approval_pendapatans', 'exam')) {
                    $table->string('exam')->nullable()->after('oleh_oleh');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approval_pendapatans', function (Blueprint $table) {
            //
        });
    }
};
