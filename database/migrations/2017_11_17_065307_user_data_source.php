<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UserDataSource extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_data_source', function (Blueprint $table) {
            $table->increments('id');
            $table->string('table_incr_id', 11)->nullable();
            $table->string('source', 50)->nullable();

            $table->unique(['table_incr_id', 'source'], 'unique_entry');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_data_source');
    }
}
