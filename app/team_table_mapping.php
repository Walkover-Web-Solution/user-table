<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class team_table_mapping extends Model
{
    protected $table = 'team_table_mapping';
    protected $fillable = ['id','table_name','table_id','team_id'];
    
    
    public static function getUserTablesByTeam($teamIdArr){
//        $userDefaultTeamId = 50;
        $data =  \DB::table('team_table_mappings')
                  ->select('*')
                  ->whereIn('team_id',$teamIdArr)
                   ->get();
        return $data;
    }
    
    public static function makeNewTableEntry($paramArr){
        $data =  \DB::table('team_table_mappings')
                  ->insert($paramArr);
        return $data;
    }
    
    public static function getUserTablesNameById($tableId){
        $data =  \DB::table('team_table_mappings')
                  ->select('*')
                  ->where('id',$tableId)
                   ->get();
        return $data;
    }
}
