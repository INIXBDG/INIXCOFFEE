<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE aktivitas MODIFY aktivitas ENUM('Call', 'Email', 'Visit', 'Meet', 'Incharge', 'PA', 'PI', 'Telemarketing', 'Form_Masuk', 'Form_Keluar', 'DB', 'Contact') NOT NULL");

        Schema::table('aktivitas', function (Blueprint $table) {
            $table->dropColumn('subject');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aktivitas', function (Blueprint $table) {
            //
        });
    }
};
