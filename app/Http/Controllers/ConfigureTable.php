<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ColumnType;

class ConfigureTable extends Controller
{
    public function getOptionList() {
        return ColumnType::all()->toArray();
    }
}
