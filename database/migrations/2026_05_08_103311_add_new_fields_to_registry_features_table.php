<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewFieldsToRegistryFeaturesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('registry_features')) {
            Schema::table('registry_features', function (Blueprint $table) {
                if (!Schema::hasColumn('registry_features', 'fakta')) {
                    $table->text('fakta')->nullable();
                }
                if (!Schema::hasColumn('registry_features', 'harapan')) {
                    $table->text('harapan')->nullable();
                }
                if (!Schema::hasColumn('registry_features', 'waktu_perkiraan')) {
                    $table->string('waktu_perkiraan')->nullable();
                }
               if (!Schema::hasColumn('registry_features', 'ticket_id')) {
                   $table->string('ticket_id')->nullable();
               }
            });
        }
    }

    public function down()
    {
        Schema::table('registry_features', function (Blueprint $table) {
            $table->dropColumn(['fakta', 'harapan', 'waktu_perkiraan', 'ticket_id']);
        });
    }
}
