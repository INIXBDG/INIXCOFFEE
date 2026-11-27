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
        if (Schema::hasTable('form_penilaians')) {
            Schema::table('form_penilaians', function (Blueprint $table) {
                if (!Schema::hasColumn('form_penilaians', 'jenis_form')) {
                    $table->string('jenis_form')->after('catatan')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('form_penilaians', function (Blueprint $table) {
            $table->dropColumn('jenis_form')->nullable();
        });
    }
};
