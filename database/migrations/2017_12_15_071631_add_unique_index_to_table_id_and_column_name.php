<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUniqueIndexToTableIdAndColumnName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_structures', function (Blueprint $table) {
            $table->unique(array('table_id', 'column_name'),'compositkey');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('table_structures', function (Blueprint $table) {
            $table->dropUnique('compositkey');
        });
    }
}
