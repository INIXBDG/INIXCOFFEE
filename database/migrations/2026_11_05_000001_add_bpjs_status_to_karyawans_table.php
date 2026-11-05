<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('karyawans', function (Blueprint $table) {
            $table->string('bpjs_status', 20)->default('active')->after('status_aktif');
            $table->timestamp('bpjs_status_changed_at')->nullable()->after('bpjs_status');
            $table->timestamp('bpjs_deleted_at')->nullable()->after('bpjs_status_changed_at');
            $table->timestamp('bpjs_restore_until')->nullable()->after('bpjs_deleted_at');
            $table->unsignedBigInteger('bpjs_deleted_by')->nullable()->after('bpjs_restore_until');

            $table->index(['bpjs_status', 'bpjs_deleted_at']);
        });
    }

    public function down(): void
    {
        Schema::table('karyawans', function (Blueprint $table) {
            $table->dropIndex(['bpjs_status', 'bpjs_deleted_at']);
            $table->dropColumn([
                'bpjs_status',
                'bpjs_status_changed_at',
                'bpjs_deleted_at',
                'bpjs_restore_until',
                'bpjs_deleted_by',
            ]);
        });
    }
};
