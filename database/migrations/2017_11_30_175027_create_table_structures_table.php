<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableStructuresTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('table_structures', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('table_id')->unsigned();
            $table->string('column_name', 60);
            $table->integer('column_type_id')->unsigned();
            $table->string('default_value', 255)->nullable();
            $table->boolean('is_unique')->default(0);
            $table->timestamps();
            $table->foreign('table_id')->references('id')->on('team_table_mappings');
            $table->foreign('column_type_id')->references('id')->on('column_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('table_structures');
    }

}
