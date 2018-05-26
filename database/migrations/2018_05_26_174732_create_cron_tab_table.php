<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCronTabTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
            Schema::create('cron_tab', function(Blueprint $table)
            {
                $table->increments('id');
                $table->integer('table_id');
                $table->integer('tab_id');
                $table->integer('type');
                $table->string('from_email', 100)->nullable();
                $table->string('from_name', 100)->nullable();
                $table->string('subject', 500)->nullable();
                $table->text('message', 65535);
                $table->integer('route');
                $table->string('tab_column_type', 100)->nullable();
                $table->timestamps();
            });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('cron_tab');
	}

}
