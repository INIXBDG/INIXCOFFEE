<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('perusahaans', 'history_sales')) {
            Schema::table('perusahaans', function (Blueprint $table) {
                $table->text('history_sales')->nullable()->after('email');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('perusahaans', 'history_sales')) {
            Schema::table('perusahaans', function (Blueprint $table) {
                $table->dropColumn('history_sales');
            });
        }
    }
};
