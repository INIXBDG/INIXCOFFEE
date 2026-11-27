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
                if (!Schema::hasColumn('approval_pendapatans', 'entertainment')) {
                    $table->bigInteger('entertainment')->nullable()->after('diskon');
                }
            });
        }

        if (Schema::hasTable('approval_pendapatan_sales')) {
            Schema::table('approval_pendapatan_sales', function (Blueprint $table) {
                if (!Schema::hasColumn('approval_pendapatan_sales', 'entertainment')) {
                    $table->bigInteger('entertainment')->nullable()->after('diskon');
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
