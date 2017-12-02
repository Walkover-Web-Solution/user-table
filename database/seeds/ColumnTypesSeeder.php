<?php

use Illuminate\Database\Seeder;
use App\ColumnType;

class ColumnTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        \DB::table('column_types')->delete();
        
        \DB::table('column_types')->insert(array(0=>
                array(
            'column_name' => 'text',
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s")
                ),1=> array('column_name' => 'phone',
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s")
                ),2=> array('column_name' => 'any number',
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s")
                ), 3=>array('column_name' => 'airthmatic number',
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s")
                ),4=> array('column_name' => 'email',
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s")
                ),5=> array('column_name' => 'dropdown',
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s")
                ),6=> array('column_name' => 'radio button',
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s")
                ),7=> array('column_name' => 'checkbox',
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s")
                ), 8=>array('column_name' => 'date',
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s")
                )
            )
        );
    }
}
