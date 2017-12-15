<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSendMailDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('send_mail_details', function (Blueprint $table) {
            $table->increments('id');
            $table->string('to_email',100)->nullable();
            $table->string('from_email',100)->nullable();
            $table->string('from_name',100)->nullable();
            $table->string('subject',500)->nullable();
            $table->text('content')->nullable();
            $table->integer('status')->unsigned()->nullable();
            $table->string('mailKey',500)->nullable();
            $table->integer('tableId')->unsigned()->nullable();
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('send_mail_details');
    }
}
