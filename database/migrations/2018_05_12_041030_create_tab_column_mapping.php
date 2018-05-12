<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTabColumnMapping extends Migration
{
    /**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
            Schema::create('tab_column_mappings', function(Blueprint $table)
            {
                    $table->increments('id');
                    $table->integer('tab_id')->unsigned();
                    $table->integer('column_id')->unsigned();
            });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('tab_column_mappings');
	}
}
