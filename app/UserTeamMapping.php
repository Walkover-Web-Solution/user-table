<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserTeamMapping extends Model {

    public function userDeatail() {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }

    public function teamDetail() {
        return $this->belongsTo('App\Team', 'team_id', 'id');
    }
}
