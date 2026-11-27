<?php

use App\Support\MigrationHelper;
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
        if (Schema::hasTable('tunjangan_karyawans')) {
            Schema::table('tunjangan_karyawans', function (Blueprint $table) {
                if (!Schema::hasColumn('tunjangan_karyawans', 'status_approval')) {
                    $table->enum('status_approval', ['pending', 'approved', 'rejected'])->default('pending')->after('total');
                }
                if (!Schema::hasColumn('tunjangan_karyawans', 'approved_by')) {
                    $table->unsignedBigInteger('approved_by')->nullable()->after('status_approval');
                }
                if (!Schema::hasColumn('tunjangan_karyawans', 'approved_at')) {
                    $table->timestamp('approved_at')->nullable()->after('approved_by');
                }
                if (!Schema::hasColumn('tunjangan_karyawans', 'rejection_note')) {
                    $table->text('rejection_note')->nullable()->after('approved_at');
                }
            });

            if (
                Schema::hasColumn('tunjangan_karyawans', 'approved_by')
                && !MigrationHelper::foreignKeyExistsOnColumn('tunjangan_karyawans', 'approved_by', 'users')
            ) {
                Schema::table('tunjangan_karyawans', function (Blueprint $table) {
                    // Foreign key ke users table untuk GM yang approve
                    $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('tunjangan_karyawans')) {
            MigrationHelper::dropForeignIfExists('tunjangan_karyawans', ['approved_by']);

            $cols = array_values(array_filter(
                ['status_approval', 'approved_by', 'approved_at', 'rejection_note'],
                fn (string $col) => Schema::hasColumn('tunjangan_karyawans', $col)
            ));

            if (!empty($cols)) {
                Schema::table('tunjangan_karyawans', function (Blueprint $table) use ($cols) {
                    $table->dropColumn($cols);
                });
            }
        }
    }
};