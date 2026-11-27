<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('approval_pendapatan_locks')) {
            Schema::table('approval_pendapatan_locks', function (Blueprint $table) {
                if (!Schema::hasColumn('approval_pendapatan_locks', 'password_approval')) {
                    $table->string('password_approval')->nullable()->after('password');
                }
                if (!Schema::hasColumn('approval_pendapatan_locks', 'password_komisi')) {
                    $table->string('password_komisi')->nullable()->after('password_approval');
                }
                if (!Schema::hasColumn('approval_pendapatan_locks', 'password_accounting')) {
                    $table->string('password_accounting')->nullable()->after('password_komisi');
                }
            });

            if (Schema::hasColumn('approval_pendapatan_locks', 'password')) {
                DB::table('approval_pendapatan_locks')
                    ->whereNotNull('password')
                    ->whereNull('password_approval')
                    ->update(['password_approval' => DB::raw('password')]);

                DB::table('approval_pendapatan_locks')
                    ->whereNotNull('password')
                    ->whereNull('password_komisi')
                    ->update(['password_komisi' => DB::raw('password')]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('approval_pendapatan_locks')) {
            $cols = array_values(array_filter(
                ['password_approval', 'password_komisi', 'password_accounting'],
                fn (string $col) => Schema::hasColumn('approval_pendapatan_locks', $col)
            ));

            if (!empty($cols)) {
                Schema::table('approval_pendapatan_locks', function (Blueprint $table) use ($cols) {
                    $table->dropColumn($cols);
                });
            }
        }
    }
};