<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Migration auto-generated by Sequel Pro Laravel Export
 * @see https://github.com/cviebrock/sequel-pro-laravel-export
 */
class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::create('users', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';
            $table->increments('id');
            $table->string('email', 255);
            $table->string('first_name',50)->nullable();
            $table->string('last_name',50)->nullable();
            $table->string('api_token', 60);
            $table->rememberToken();
            $table->nullableTimestamps();
            $table->string('company', 50)->nullable();
            $table->string('identifier', 50)->nullable();

            $table->unique('email', 'users_email_unique');
            $table->unique('api_token', 'users_api_token_unique');

            

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
