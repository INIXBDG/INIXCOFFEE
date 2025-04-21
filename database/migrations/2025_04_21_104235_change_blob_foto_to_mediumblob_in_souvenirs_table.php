<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ChangeBlobFotoToMediumblobInSouvenirsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('souvenirs', function (Blueprint $table) {
            $table->binary('blob_foto')->change();
        });

        // Mengubah tipe kolom menjadi MEDIUMBLOB menggunakan raw SQL
        DB::statement('ALTER TABLE souvenirs MODIFY COLUMN blob_foto MEDIUMBLOB');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('souvenirs', function (Blueprint $table) {
            $table->binary('blob_foto')->change();
        });

        // Mengubah tipe kolom kembali ke BLOB menggunakan raw SQL
        DB::statement('ALTER TABLE souvenirs MODIFY COLUMN blob_foto BLOB');
    }
}

