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
        if (!Schema::hasTable('perusahaans')) {
            return;
        }

        if (Schema::hasColumn('perusahaans', 'history_sales')) {
            return;
        }

        $hasEmail = Schema::hasColumn('perusahaans', 'email');

        Schema::table('perusahaans', function (Blueprint $table) use ($hasEmail) {
            if ($hasEmail) {
                $table->text('history_sales')->nullable()->after('email');
            } else {
                $table->text('history_sales')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('perusahaans')) {
            return;
        }

        if (!Schema::hasColumn('perusahaans', 'history_sales')) {
            return;
        }

        Schema::table('perusahaans', function (Blueprint $table) {
            $table->dropColumn('history_sales');
        });
    }
};