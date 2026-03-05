<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE content_schedules
            MODIFY content_form
            ENUM('Reels', 'Youtube', 'Feed', 'Story', 'Tiktok')
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE content_schedules
            MODIFY content_form
            ENUM('Reels', 'Youtube', 'Feed', 'Story')
        ");
    }
};
