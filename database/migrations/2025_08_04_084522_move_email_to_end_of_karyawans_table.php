<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Drop kolom kalau memang ada
        if (Schema::hasColumn('karyawans', 'email')) {
            Schema::table('karyawans', function (Blueprint $table) {
                $table->dropColumn('email');
            });
        }

        // Tambahkan ulang kolom email di akhir
        Schema::table('karyawans', function (Blueprint $table) {
            $table->string('email')->nullable();
        });
    }

    public function down()
    {
        // Drop kalau ada
        if (Schema::hasColumn('karyawans', 'email')) {
            Schema::table('karyawans', function (Blueprint $table) {
                $table->dropColumn('email');
            });
        }

        // Tambahkan kembali di posisi setelah nama_lengkap
        Schema::table('karyawans', function (Blueprint $table) {
            $table->string('email')->nullable()->after('nama_lengkap');
        });
    }
};
