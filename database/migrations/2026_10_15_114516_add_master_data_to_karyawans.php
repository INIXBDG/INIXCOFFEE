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
                if (!Schema::hasColumn('karyawans', 'KTP')) {
                    $table->string('KTP')->nullable()->after('kode_karyawan');
                }
                if (!Schema::hasColumn('karyawans', 'SIM_A')) {
                    $table->string('SIM_A')->nullable()->after('KTP');
                }
                if (!Schema::hasColumn('karyawans', 'SIM_B')) {
                    $table->string('SIM_B')->nullable()->after('SIM_A');
                }
                if (!Schema::hasColumn('karyawans', 'SIM_C')) {
                    $table->string('SIM_C')->nullable()->after('SIM_B');
                }
                if (!Schema::hasColumn('karyawans', 'Ijazah')) {
                    $table->string('Ijazah')->nullable()->after('SIM_C');
                }
                if (!Schema::hasColumn('karyawans', 'CV')) {
                    $table->string('CV')->nullable()->after('Ijazah');
                }
                if (!Schema::hasColumn('karyawans', 'NPWP')) {
                    $table->string('NPWP')->nullable()->after('CV');
                }
                if (!Schema::hasColumn('karyawans', 'doc_kontrak')) {
                    $table->string('doc_kontrak')->nullable()->after('NPWP');
                }
                if (!Schema::hasColumn('karyawans', 'doc_probation')) {
                    $table->string('doc_probation')->nullable()->after('doc_kontrak');
                }
                if (!Schema::hasColumn('karyawans', 'skema_gaji')) {
                    $table->string('skema_gaji')->nullable()->after('doc_probation');
                }
                if (!Schema::hasColumn('karyawans', 'status_karyawan')) {
                    $table->string('status_karyawan')->nullable()->after('skema_gaji');
                }
                if (!Schema::hasColumn('karyawans', 'KK')) {
                    $table->string('KK')->nullable()->after('status_karyawan');
                }
                if (!Schema::hasColumn('karyawans', 'nik')) {
                    $table->string('nik')->nullable()->after('KK');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('karyawans', function (Blueprint $table) {
            $table->dropColumn([
                'KTP',
                'SIM',
                'Ijazah',
                'CV',
                'NPWP',
                'doc_kontrak',
                'doc_probation',
                'skema_gaji',
                'status_karyawan',
                'KK'
            ]);
        });
    }
};
