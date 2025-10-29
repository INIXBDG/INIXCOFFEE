<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::table('target_activities', function (Blueprint $table) {
            $table->date('deadline')->nullable()->after('FormK');
        });
    }

    public function down(): void
    {
    }

};
