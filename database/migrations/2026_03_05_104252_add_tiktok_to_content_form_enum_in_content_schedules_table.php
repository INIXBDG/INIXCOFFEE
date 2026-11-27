<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('content_schedules') && Schema::hasColumn('content_schedules', 'content_form')) {
            DB::statement("
                ALTER TABLE content_schedules
                MODIFY content_form
                ENUM('Reels', 'Youtube', 'Feed', 'Story', 'Tiktok')
            ");
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('content_schedules') && Schema::hasColumn('content_schedules', 'content_form')) {
            DB::statement("
                ALTER TABLE content_schedules
                MODIFY content_form
                ENUM('Reels', 'Youtube', 'Feed', 'Story')
            ");
        }
    }
};
