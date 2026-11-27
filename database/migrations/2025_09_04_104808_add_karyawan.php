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
        if (Schema::hasTable('karyawans')) {
            Schema::table('karyawans', function (Blueprint $table) {
                if (!Schema::hasColumn('karyawans', 'email')) {
                    $table->text('email')->nullable();
                }
                if (!Schema::hasColumn('karyawans', 'whatsapp')) {
                    $table->string('whatsapp')->nullable();
                }
                if (!Schema::hasColumn('karyawans', 'telepon')) {
                    $table->string('telepon')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('karyawans')) {
            $cols = array_values(array_filter(
                ['whatsapp', 'telepon'],
                fn (string $col) => Schema::hasColumn('karyawans', $col)
            ));

            if (!empty($cols)) {
                Schema::table('karyawans', function (Blueprint $table) use ($cols) {
                    $table->dropColumn($cols);
                });
            }
        }
    }
};
