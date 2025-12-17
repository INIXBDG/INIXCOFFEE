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

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->string('foto')->nullable();
                $table->text('keterangan')->nullable();
            });
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

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn(['foto', 'keterangan']);
            });
        }
    }
};
