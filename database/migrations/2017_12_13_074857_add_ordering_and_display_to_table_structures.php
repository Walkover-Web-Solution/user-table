<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOrderingAndDisplayToTableStructures extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_structures', function (Blueprint $table) {
            $table->boolean('display');
            $table->integer('ordering');
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
            $table->dropColumn('display');
            $table->dropColumn('ordering');
        });
    }
}
