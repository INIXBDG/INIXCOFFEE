<?php

use App\Support\MigrationHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('org_charts')) {
            Schema::create('org_charts', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->timestamps();
            });
        }

        if (Schema::hasTable('org_structures')) {
            Schema::table('org_structures', function (Blueprint $table) {
                if (!Schema::hasColumn('org_structures', 'chart_id')) {
                    $table->foreignId('chart_id')
                        ->nullable()
                        ->after('id')
                        ->constrained('org_charts')
                        ->cascadeOnDelete();
                }

                if (!Schema::hasColumn('org_structures', 'pos_x')) {
                    $table->integer('pos_x')->nullable();
                }

                if (!Schema::hasColumn('org_structures', 'pos_y')) {
                    $table->integer('pos_y')->nullable();
                }
            });

            if (DB::table('org_structures')->whereNull('chart_id')->exists()) {
                $chartId = DB::table('org_charts')->value('id') ?? DB::table('org_charts')->insertGetId([
                    'name' => 'Struktur Utama',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('org_structures')->whereNull('chart_id')->update(['chart_id' => $chartId]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('org_structures')) {
            $dropCols = array_values(array_filter(
                ['pos_x', 'pos_y'],
                fn (string $col) => Schema::hasColumn('org_structures', $col)
            ));
            if (!empty($dropCols)) {
                Schema::table('org_structures', function (Blueprint $table) use ($dropCols) {
                    $table->dropColumn($dropCols);
                });
            }

            MigrationHelper::dropForeignIfExists('org_structures', ['chart_id']);
            if (Schema::hasColumn('org_structures', 'chart_id')) {
                Schema::table('org_structures', function (Blueprint $table) {
                    $table->dropColumn('chart_id');
                });
            }
        }

        Schema::dropIfExists('org_charts');
    }
};