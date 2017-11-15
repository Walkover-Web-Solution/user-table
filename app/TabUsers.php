<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TabUsers extends Model
{
    protected $table = 'tab_users';
    protected $fillable = [ 'tab_name', 'users_list','created_at','updated_at'];

    public static function getOldUsersList($tab){
        $data = TabUsers::where('tab_name','=',$tab)
        ->first(['users_list']);
        return $data;
    }

    public static function updateTabUsersList($tab,$update){
        $data  = TabUsers::where('tab_name', $tab)
            ->update($update);
    }

}
