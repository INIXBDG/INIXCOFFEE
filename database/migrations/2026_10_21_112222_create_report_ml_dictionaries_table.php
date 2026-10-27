<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('report_ml_dictionaries')) {
            Schema::create('report_ml_dictionaries', function (Blueprint $t) {
                $t->id();
                $t->string('context_keyword', 191)->unique();
                $t->string('field_type', 50)->default('db');
                $t->text('field_value')->nullable();
                $t->unsignedInteger('hit_count')->default(1);
                $t->timestamp('last_used_at')->nullable();
                $t->unsignedBigInteger('created_by')->nullable();
                $t->timestamps();
            });

            return;
        }

        Schema::table('report_ml_dictionaries', function (Blueprint $t) {
            if (!Schema::hasColumn('report_ml_dictionaries', 'hit_count')) {
                $t->unsignedInteger('hit_count')->default(1);
            }
            if (!Schema::hasColumn('report_ml_dictionaries', 'last_used_at')) {
                $t->timestamp('last_used_at')->nullable();
            }
            if (!Schema::hasColumn('report_ml_dictionaries', 'created_by')) {
                $t->unsignedBigInteger('created_by')->nullable();
            }
        });

        try {
            Schema::table('report_ml_dictionaries', function (Blueprint $t) {
                $t->text('field_value')->nullable()->change();
            });
        } catch (\Throwable $e) {
        }
    }

    public function down(): void
    {
    }
};