<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('org_charts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::table('org_structures', function (Blueprint $table) {
            $table->foreignId('chart_id')
                ->nullable()
                ->after('id')
                ->constrained('org_charts')
                ->cascadeOnDelete();

            $table->integer('pos_x')->nullable();
            $table->integer('pos_y')->nullable();
        });

        if (DB::table('org_structures')->exists()) {
            $chartId = DB::table('org_charts')->insertGetId([
                'name' => 'Struktur Utama',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('org_structures')->update(['chart_id' => $chartId]);
        }
    }

    public function down(): void
    {
        Schema::table('org_structures', function (Blueprint $table) {
            $table->dropColumn(['pos_x', 'pos_y']);
            $table->dropConstrainedForeignId('chart_id');
        });

        Schema::dropIfExists('org_charts');
    }
};