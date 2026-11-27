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
        if (Schema::hasTable('certificates')) {
            Schema::table('certificates', function (Blueprint $table) {
                if (!Schema::hasColumn('certificates', 'tanggal_pelatihan2')) {
                    $table->string('tanggal_pelatihan2')->nullable()->after('tanggal_pelatihan');
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
