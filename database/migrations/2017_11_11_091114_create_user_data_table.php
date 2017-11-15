<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUserDataTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('user_data', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('username', 30)->unique();
			$table->string('firstname', 20)->nullable();
			$table->string('lastname', 20)->nullable();
			$table->string('email', 50)->nullable();
			$table->string('city', 20)->nullable();
			$table->string('country', 20)->nullable();
			$table->string('contact', 15)->nullable();
			$table->string('source', 100)->nullable();
			$table->date('follow_up_date')->nullable();
			$table->string('assign_to', 50)->nullable();
			$table->string('utm_source', 50)->nullable();
			$table->string('utm_campaign', 50)->nullable();
			$table->string('reference', 50)->nullable();
			$table->softDeletes();
			$table->timestamps();
			$table->string('status', 100)->nullable();
			$table->string('purpose', 100)->nullable();
			$table->string('industry', 100)->nullable();
			$table->string('true_client', 100)->nullable();
			$table->string('won_or_lost', 100)->nullable();
			$table->text('comment', 65535)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('user_data');
	}

}
