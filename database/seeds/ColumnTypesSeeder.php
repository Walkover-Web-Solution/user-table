<?php

use Illuminate\Database\Seeder;
use App\ColumnType;

class ColumnTypesSeeder extends Seeder {

    private $columnNames = ['text', 'phone', 'any number', 'airthmatic number', 'email', 'dropdown', 'radio button', 'checkbox', 'date','my teammates','long text'];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        foreach ($this->columnNames as $key => $name) {
            $insertArray = array(
                'id' => $key+1,
                'column_name' => $name,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            );
            ColumnType::firstOrCreate(array('id'=>$key+1),$insertArray);
        }
    }
}
