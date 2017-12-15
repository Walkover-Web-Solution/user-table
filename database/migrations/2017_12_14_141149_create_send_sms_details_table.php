<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSendSmsDetailsTable extends Migration
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
            $table->string('senderId',15)->nullable();
            $table->text('message',100)->nullable();
            $table->string('number',20)->nullable();
            $table->string('authKey',30)->nullable();
            $table->integer('route',11)->nullable();
            $table->integer('status',10)->nullable();
            $table->integer('tableId',10)->nullable();
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('send_sms_details');
    }
}
