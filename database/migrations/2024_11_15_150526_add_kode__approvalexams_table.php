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
        if (Schema::hasTable('approvalexams')) {
            Schema::table('approvalexams', function (Blueprint $table) {
                if (!Schema::hasColumn('approvalexams', 'ttd_sales')) {
                    $table->string('ttd_sales')->nullable();
                }
                if (!Schema::hasColumn('approvalexams', 'ttd_ts')) {
                    $table->string('ttd_ts')->nullable();
                }
                if (!Schema::hasColumn('approvalexams', 'ttd_off')) {
                    $table->string('ttd_off')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approvalexams', function (Blueprint $table) {
            //
        });
    }
};
