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
        if (Schema::hasTable('feature_documentations')) {
            $dropCols = array_values(array_filter(
                ['background', 'manual_file_path', 'manual_file_name'],
                fn (string $col) => Schema::hasColumn('feature_documentations', $col)
            ));
            if (!empty($dropCols)) {
                Schema::table('feature_documentations', function (Blueprint $table) use ($dropCols) {
                    $table->dropColumn($dropCols);
                });
            }

            Schema::table('feature_documentations', function (Blueprint $table) {
                if (!Schema::hasColumn('feature_documentations', 'update_by')) {
                    $table->integer('update_by')->after('user_access')->nullable();
                }
                if (!Schema::hasColumn('feature_documentations', 'log_update')) {
                    $table->json('log_update')->after('update_by')->nullable();
                }
                if (!Schema::hasColumn('feature_documentations', 'log_time_update')) {
                    $table->json('log_time_update')->after('log_update')->nullable();
                }
                if (!Schema::hasColumn('feature_documentations', 'parent_id')) {
                    $table->foreignId('parent_id')->nullable()->after('id')->constrained('feature_documentations')->nullOnDelete();
                }
            });
        }

        if (Schema::hasTable('code_documentations')) {
            Schema::table('code_documentations', function (Blueprint $table) {
                if (!Schema::hasColumn('code_documentations', 'update_by')) {
                    $table->integer('update_by')->after('future_development')->nullable();
                }
                if (!Schema::hasColumn('code_documentations', 'log_update')) {
                    $table->json('log_update')->after('update_by')->nullable();
                }
                if (!Schema::hasColumn('code_documentations', 'log_time_update')) {
                    $table->json('log_time_update')->after('log_update')->nullable();
                }
                if (!Schema::hasColumn('code_documentations', 'log_changes')) {
                    $table->json('log_changes')->after('log_time_update')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('feature_documentations')) {
            MigrationHelper::dropForeignIfExists('feature_documentations', ['parent_id']);
            if (Schema::hasColumn('feature_documentations', 'parent_id')) {
                Schema::table('feature_documentations', function (Blueprint $table) {
                    $table->dropColumn('parent_id');
                });
            }
        }
    }
};
