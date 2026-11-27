<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('content_schedules')) {
            Schema::table('content_schedules', function (Blueprint $table) {
                if (Schema::hasColumn('content_schedules', 'talents')) {
                    $table->string('talents')->nullable()->change();
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('content_schedules', function (Blueprint $table) {
            $table->string('talents')->nullable(false)->change();
        });
    }
};
