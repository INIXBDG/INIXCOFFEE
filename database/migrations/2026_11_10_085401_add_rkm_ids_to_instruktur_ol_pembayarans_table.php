
<?php

use App\Support\MigrationHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('instruktur_ol_pembayarans') || !Schema::hasColumn('instruktur_ol_pembayarans', 'rkm_id')) {
            return;
        }

        MigrationHelper::dropIndexIfExists('instruktur_ol_pembayarans', ['rkm_id']);

        if (MigrationHelper::columnDataType('instruktur_ol_pembayarans', 'rkm_id') !== 'json') {
            Schema::table('instruktur_ol_pembayarans', function (Blueprint $table) {
                $table->json('rkm_id')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('instruktur_ol_pembayarans') || !Schema::hasColumn('instruktur_ol_pembayarans', 'rkm_id')) {
            return;
        }

        Schema::table('instruktur_ol_pembayarans', function (Blueprint $table) {
            $table->unsignedBigInteger('rkm_id')->nullable()->change();
        });

        MigrationHelper::addIndexIfNotExists('instruktur_ol_pembayarans', 'rkm_id');
    }
};  