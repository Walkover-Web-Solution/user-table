<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTabIdToSendSms extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('send_sms_details', function (Blueprint $table) {
            $table->integer('tab_id');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('send_sms_details', function (Blueprint $table) {
            $table->dropColumn('tab_id');
        });
    }
}
