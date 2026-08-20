<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('approval_pendapatan_locks', function (Blueprint $table) {
            $table->string('password_approval')->nullable()->after('password');
            $table->string('password_komisi')->nullable()->after('password_approval');

            $table->string('password_accounting')->nullable()->after('password_komisi');
        });

        DB::table('approval_pendapatan_locks')->whereNotNull('password')->update([
            'password_approval' => DB::raw('password'),
            'password_komisi'   => DB::raw('password'),
        ]);
    }

    public function down(): void
    {
        Schema::table('approval_pendapatan_locks', function (Blueprint $table) {
            $table->dropColumn(['password_approval', 'password_komisi', 'password_accounting']);
        });
    }
};