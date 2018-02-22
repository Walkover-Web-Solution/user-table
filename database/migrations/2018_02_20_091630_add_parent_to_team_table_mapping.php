<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddParentToTeamTableMapping extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('team_table_mappings', function (Blueprint $table) {
            $table->integer('parent_table_id');
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
            $table->dropColumn('parent_table_id');
        });
    }
}
