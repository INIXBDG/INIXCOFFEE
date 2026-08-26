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
        
        Schema::table('feature_documentations', function (Blueprint $table) {
            $table->dropColumn([
                'background',
                'manual_file_path',
                'manual_file_name',
            ]);
        });

        Schema::table('feature_documentations', function (Blueprint $table) {
            $table->integer('update_by')->after('user_access')->nullable();
            $table->json('log_update')->after('update_by')->nullable();
            $table->json('log_time_update')->after('log_update')->nullable();
            $table->foreignId('parent_id')->nullable()->after('id')->constrained('feature_documentations')->nullOnDelete();
        });

        Schema::table('code_documentations', function (Blueprint $table) {
            $table->integer('update_by')->after('future_development')->nullable();
            $table->json('log_update')->after('update_by')->nullable();
            $table->json('log_time_update')->after('log_update')->nullable();
            $table->json('log_changes')->after('log_time_update')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('feature_documentation', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_id');
        });
    }
};
