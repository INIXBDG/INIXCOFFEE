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
        if (Schema::hasTable('checklist_keperluans')) {
            Schema::table('checklist_keperluans', function (Blueprint $table) {
                if (!Schema::hasColumn('checklist_keperluans', 'tanggal_keperluan')) {
                    $table->date('tanggal_keperluan')->nullable()->after('id_rkm');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('checklist_keperluans', function (Blueprint $table) {
            //
        });
    }
};
