<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        if (Schema::hasTable('target_activities')) {
            Schema::table('target_activities', function (Blueprint $table) {
                if (!Schema::hasColumn('target_activities', 'deadline')) {
                    $table->date('deadline')->nullable()->after('FormK');
                }
            });
        }
    }

    public function down(): void
    {
    }

};
