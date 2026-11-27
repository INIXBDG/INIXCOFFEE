<?php

use App\Support\MigrationHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('karyawans')) {
            Schema::table('karyawans', function (Blueprint $table) {
                if (!Schema::hasColumn('karyawans', 'bpjs_status')) {
                    $table->string('bpjs_status', 20)->default('active')->after('status_aktif');
                }
                if (!Schema::hasColumn('karyawans', 'bpjs_status_changed_at')) {
                    $table->timestamp('bpjs_status_changed_at')->nullable()->after('bpjs_status');
                }
                if (!Schema::hasColumn('karyawans', 'bpjs_deleted_at')) {
                    $table->timestamp('bpjs_deleted_at')->nullable()->after('bpjs_status_changed_at');
                }
                if (!Schema::hasColumn('karyawans', 'bpjs_restore_until')) {
                    $table->timestamp('bpjs_restore_until')->nullable()->after('bpjs_deleted_at');
                }
                if (!Schema::hasColumn('karyawans', 'bpjs_deleted_by')) {
                    $table->unsignedBigInteger('bpjs_deleted_by')->nullable()->after('bpjs_restore_until');
                }
            });

            MigrationHelper::addIndexIfNotExists('karyawans', ['bpjs_status', 'bpjs_deleted_at']);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('karyawans')) {
            MigrationHelper::dropIndexIfExists('karyawans', ['bpjs_status', 'bpjs_deleted_at']);

            $cols = array_values(array_filter([
                'bpjs_status',
                'bpjs_status_changed_at',
                'bpjs_deleted_at',
                'bpjs_restore_until',
                'bpjs_deleted_by',
            ], fn (string $col) => Schema::hasColumn('karyawans', $col)));

            if (!empty($cols)) {
                Schema::table('karyawans', function (Blueprint $table) use ($cols) {
                    $table->dropColumn($cols);
                });
            }
        }
    }
};
