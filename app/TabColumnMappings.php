<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TabColumnMappings extends Model {

    protected $table = 'tab_column_mappings';
    public $timestamps = false;
    protected $fillable = ['tab_id', 'column_id'];
}