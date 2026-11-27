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
        if (Schema::hasTable('log_gajis')) {
            Schema::table('log_gajis', function (Blueprint $table) {
                if (!Schema::hasColumn('log_gajis', 'tunjangan_jabatan')) {
                    $table->decimal('tunjangan_jabatan', 15, 2)->default(0)->after('gaji');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('log_gajis', function (Blueprint $table) {
            $table->dropColumn('tunjangan_jabatan');
        });
    }
};
