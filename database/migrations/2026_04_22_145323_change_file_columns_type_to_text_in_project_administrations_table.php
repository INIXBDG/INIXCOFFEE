<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('project_administrations', function (Blueprint $table) {
            // Mengubah tipe kolom menjadi teks panjang untuk menampung JSON array
            $table->text('kak_file')->nullable()->change();
            $table->text('budget_file')->nullable()->change();
            $table->text('proposal_file')->nullable()->change();
            $table->text('client_doc_file')->nullable()->change();
            $table->text('payment_doc_file')->nullable()->change();
            $table->text('surat_pekerjaan_dimulai_file')->nullable()->change();
            // Tambahkan kolom lain jika ada, misalnya proposal_file atau surat_pekerjaan_dimulai_file
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('project_administrations', function (Blueprint $table) {
            // Mengembalikan ke string (VARCHAR 255)
            $table->string('kak_file')->nullable()->change();
            $table->string('budget_file')->nullable()->change();
            $table->string('proposal_file')->nullable()->change();
            $table->string('client_doc_file')->nullable()->change();
            $table->string('payment_doc_file')->nullable()->change();
            $table->string('surat_pekerjaan_dimulai_file')->nullable()->change();
        });
    }
};