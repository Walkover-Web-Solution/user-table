<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User_data_source extends Model
{
    protected $table = 'user_data_source';
    protected $fillable = ['id', 'table_incr_id', 'source'];
}
