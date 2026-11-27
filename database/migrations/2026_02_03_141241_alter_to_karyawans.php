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
                if (!Schema::hasColumn('karyawans', 'alamat_lengkap')) {
                    $table->text('alamat_lengkap')->nullable()->after('nama_lengkap');
                }
                if (!Schema::hasColumn('karyawans', 'gender')) {
                    $table->string('gender', 20)->nullable()->after('alamat_lengkap');
                }
                if (!Schema::hasColumn('karyawans', 'tempat_lahir')) {
                    $table->string('tempat_lahir')->nullable()->after('gender');
                }
                if (!Schema::hasColumn('karyawans', 'tanggal_lahir')) {
                    $table->date('tanggal_lahir')->nullable()->after('tempat_lahir');
                }
                if (!Schema::hasColumn('karyawans', 'religion')) {
                    $table->string('religion', 50)->nullable()->after('tanggal_lahir');
                }
                if (!Schema::hasColumn('karyawans', 'provinsi')) {
                    $table->string('provinsi')->nullable()->after('religion');
                }
                if (!Schema::hasColumn('karyawans', 'kota')) {
                    $table->string('kota')->nullable()->after('provinsi');
                }
            });
        }
    }
};
