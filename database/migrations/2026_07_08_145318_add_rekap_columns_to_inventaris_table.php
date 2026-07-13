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
        Schema::table('inventaris', function (Blueprint $table) {
            if (!Schema::hasColumn('inventaris', 'idinventaris')) {
                $table->string('idinventaris')->nullable()->after('idbarang');
            }
            if (!Schema::hasColumn('inventaris', 'kategori')) {
                $table->string('kategori')->nullable()->after('name');
            }
            if (!Schema::hasColumn('inventaris', 'no_kk')) {
                $table->string('no_kk')->nullable()->after('ruangan');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventaris', function (Blueprint $table) {
            $table->dropColumn(['idinventaris', 'kategori', 'no_kk']);
        });
    }
};
