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
        if (Schema::hasTable('registexams')) {
            Schema::table('registexams', function (Blueprint $table) {
                if (!Schema::hasColumn('registexams', 'email_exam')) {
                    $table->string('email_exam')->nullable();
                }
                if (!Schema::hasColumn('registexams', 'akun_exam')) {
                    $table->text('akun_exam')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registexams', function (Blueprint $table) {
            //
        });
    }
};
