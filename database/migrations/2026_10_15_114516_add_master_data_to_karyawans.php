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
        Schema::table('karyawans', function (Blueprint $table) {
            $table->string('KTP')->nullable()->after('kode_karyawan');
            $table->string('SIM_A')->nullable()->after('KTP');
            $table->string('SIM_B')->nullable()->after('SIM_A');
            $table->string('SIM_C')->nullable()->after('SIM_B');
            $table->string('Ijazah')->nullable()->after('SIM_C');
            $table->string('CV')->nullable()->after('Ijazah');
            $table->string('NPWP')->nullable()->after('CV');
            $table->string('doc_kontrak')->nullable()->after('NPWP');
            $table->string('doc_probation')->nullable()->after('doc_kontrak');
            $table->string('skema_gaji')->nullable()->after('doc_probation');
            $table->string('status_karyawan')->nullable()->after('skema_gaji');
            $table->string('KK')->nullable()->after('status_karyawan');
            $table->string('nik')->nullable()->after('KK');
        });
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
