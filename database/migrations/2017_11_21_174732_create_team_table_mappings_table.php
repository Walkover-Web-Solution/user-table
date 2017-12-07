<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTeamTableMappingsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('team_table_mappings', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('table_name', 60)->nullable();
			$table->string('table_id', 60)->nullable();
			$table->string('team_id', 60)->nullable();
			$table->text('table_structure', 65535)->nullable();
			$table->string('auth', 20)->nullable()->default('');
			$table->string('socket_api', 200)->nullable()->default(''); 
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('team_table_mappings');
	}

}
