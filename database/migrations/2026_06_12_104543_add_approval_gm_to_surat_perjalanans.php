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
        if (Schema::hasTable('surat_perjalanans')) {
            Schema::table('surat_perjalanans', function (Blueprint $table) {
                if (!Schema::hasColumn('surat_perjalanans', 'approval_gm')) {
                    $table->enum('approval_gm', ['0', '1', '2'])->default('0')->after('approval_hrd');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_perjalanans', function (Blueprint $table) {
            $table->dropColumn('approval_gm');
        });
    }
};
