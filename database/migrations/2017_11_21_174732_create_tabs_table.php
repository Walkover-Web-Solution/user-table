<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTabsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tabs', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('tab_name', 100)->unique();
			$table->text('query', 65535);
			$table->text('webhook', 65535)->nullable();
			$table->timestamps();
			$table->string('table_id', 60)->nullable()->default('NOT NULL');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('tabs');
	}

}
