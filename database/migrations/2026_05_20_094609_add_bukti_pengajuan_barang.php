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
        if (Schema::hasTable('pengajuanbarangs')) {
            Schema::table('pengajuanbarangs', function (Blueprint $table) {
                if (!Schema::hasColumn('pengajuanbarangs', 'bukti')) {
                    $table->string('bukti')->nullable()->after('invoice');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengajuanbarangs', function (Blueprint $table) {
            $table->dropColumn('bukti');
        });
    }
};
