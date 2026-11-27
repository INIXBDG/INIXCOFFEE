<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'vendor_bengkels',
            'vendor_coffee_breaks',
            'vendor_makansiangs',
            'vendor_souvenirs',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (!Schema::hasColumn($tableName, 'foto')) {
                        $table->string('foto')->nullable();
                    }
                    if (!Schema::hasColumn($tableName, 'keterangan')) {
                        $table->text('keterangan')->nullable();
                    }
                });
            }
        }
    }

    public function down(): void
    {
        $tables = [
            'vendor_bengkels',
            'vendor_coffee_breaks',
            'vendor_makansiangs',
            'vendor_souvenirs',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                $cols = array_values(array_filter(
                    ['foto', 'keterangan'],
                    fn (string $col) => Schema::hasColumn($tableName, $col)
                ));
                if (!empty($cols)) {
                    Schema::table($tableName, function (Blueprint $table) use ($cols) {
                        $table->dropColumn($cols);
                    });
                }
            }
        }
    }
};
