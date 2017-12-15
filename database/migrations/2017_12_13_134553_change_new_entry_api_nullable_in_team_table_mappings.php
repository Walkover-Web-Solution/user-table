<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeNewEntryApiNullableInTeamTableMappings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('team_table_mappings', function (Blueprint $table) {
            $table->string('new_entry_api')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('team_table_mappings', function (Blueprint $table) {
            $table->string('new_entry_api')->nullable(false)->change();
        });
    }
}
