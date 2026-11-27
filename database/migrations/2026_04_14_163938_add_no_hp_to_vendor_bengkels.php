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
        if (Schema::hasTable('vendor_bengkels')) {
            Schema::table('vendor_bengkels', function (Blueprint $table) {
                if (!Schema::hasColumn('vendor_bengkels', 'no_hp')) {
                    $table->string('no_hp')->nullable();
                }
                if (!Schema::hasColumn('vendor_bengkels', 'no_rekening')) {
                    $table->string('no_rekening')->nullable();
                }
                if (!Schema::hasColumn('vendor_bengkels', 'alamat')) {
                    $table->text('alamat')->nullable();
                }
            });
        }

        if (Schema::hasTable('perbaikan_kendaraans')) {
            Schema::table('perbaikan_kendaraans', function (Blueprint $table) {
                if (!Schema::hasColumn('perbaikan_kendaraans', 'id_vendor')) {
                    $table->string('id_vendor')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_bengkels', function (Blueprint $table) {
            //
        });
    }
};
