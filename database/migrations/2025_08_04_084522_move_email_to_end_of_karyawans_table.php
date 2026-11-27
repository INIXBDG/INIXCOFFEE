<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('karyawans')) {
            return;
        }

        // Tambahkan kolom email di akhir hanya jika belum ada (hindari dropColumn agar tidak menghapus data existing)
        if (!Schema::hasColumn('karyawans', 'email')) {
            Schema::table('karyawans', function (Blueprint $table) {
                $table->string('email')->nullable();
            });
        }
    }

    public function down()
    {
        // Tidak menghapus kolom email agar data karyawan tetap aman saat rollback
    }
};
