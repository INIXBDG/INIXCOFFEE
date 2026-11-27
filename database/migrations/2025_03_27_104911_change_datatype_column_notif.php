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
        if (Schema::hasTable('notifs')) {
            Schema::table('notifs', function (Blueprint $table) {

                if (Schema::hasColumn('notifs', 'tanggal_awal')) {
                    $table->date('tanggal_awal')->change();
                }
                if (Schema::hasColumn('notifs', 'tanggal_akhir')) {
                    $table->date('tanggal_akhir')->change();
                }

            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
