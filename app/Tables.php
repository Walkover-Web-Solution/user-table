<?php

namespace App;

use App\Classes\Utility;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;

class Tables extends Model
{
    public static function getFiltrableData($tableId)
    {
        $forStr = array('is' => null,
                        'is_not' =>null,
                        'contains'=>null,
                        'not_contains' =>null
                        );
        $forInt = array( 'less_than'=> null,
                         'greater_than'=> null,
                         'equals_to'=> null);
        $forDate = array('from' =>null,'to' => null);
        $data = array();
        $table_info_columns =  \DB::select("SHOW COLUMNS FROM `$tableId` WHERE FIELD NOT IN ('id')");
        $tabQuery  = (array) json_decode(Tabs::getTabsByTableId($tableId));
        $tabQuery = array();

        foreach ($table_info_columns as $column) {
            $col_name = $column->Field;
            $col_type = $column->Type;

            if(isset($tabQuery[$col_name]))
            {
                //for string fields
               if(isset($tabQuery[$col_name]->is))
                   $data[$col_name]['is'] = $tabQuery[$col_name]->is;

               else  if(isset($tabQuery[$col_name]->is_not))
                   $data[$col_name]['is_not'] = $tabQuery[$col_name]->is_not;

               else  if(isset($tabQuery[$col_name]->contains))
                   $data[$col_name]['contains'] = $tabQuery[$col_name]->contains;

               else  if(isset($tabQuery[$col_name]->not_contains))
                   $data[$col_name]['not_contains'] = $tabQuery[$col_name]->not_contains;

               //for int fields
               else  if(isset($tabQuery[$col_name]->less_than))
                   $data[$col_name]['less_than'] = $tabQuery[$col_name]->less_than;

               else  if(isset($tabQuery[$col_name]->greater_than))
                   $data[$col_name]['greater_than'] = $tabQuery[$col_name]->greater_than;

               else  if(isset($tabQuery[$col_name]->equals_to))
                   $data[$col_name]['equals_to'] = $tabQuery[$col_name]->equals_to;

               // for dates
               else  if(isset($tabQuery[$col_name]->from))
                   $data[$col_name]['from'] = $tabQuery[$col_name]->from;

               else  if(isset($tabQuery[$col_name]->to))
                   $data[$col_name]['to'] = $tabQuery[$col_name]->to;
            }
            else{
                if (strpos($col_type, 'varchar') !== false) {
                    $data[$col_name] =  $forStr;
                }
                else if (strpos($col_type, 'int') !== false) {
                    $data[$col_name] =  $forInt;
                }
                else if(strpos($col_type, 'timestamp') !== false)
                   $data[$col_name] =  $forDate;
            }
        }
        return $data;
    }
    
    public static function TabDataBySavedFilter($tableId,$tabName){
        if($tabName == "All")
         {
             $data = \DB::table($tableId)->selectRaw('*')->get();
         }
         else{
            $tabSql = Tabs::where([['tab_name', $tabName],['table_id', $tableId]])->first(['query']);
            $req  =  (array)json_decode($tabSql->query);
            $data = Tables::getFilteredUsersDetailsData($req,$tableId);
         }
        return $data;
    }
    
    
     public static function getFilteredUsersDetailsData($req,$tableId){
         
         $users =  \DB::table($tableId)->selectRaw('*');
        foreach(array_keys($req) as $paramName) {
            
            if (isset($req[$paramName]->is)) {
                $users->where($paramName,'=',$req[$paramName]->is);
            }
            else if (isset($req[$paramName]->is_not))
            {
                $users->where($paramName,'<>',$req[$paramName]->is_not);
            }

            else if (isset($req[$paramName]->contains)) {
                //dd($req[$paramName]->contains);
                $users->where($paramName,'LIKE','%'.$req[$paramName]->contains.'%');
            }
            else if (isset($req[$paramName]->not_contains)) {
                $users->where($paramName,'LIKE','%'.$req[$paramName]->not_contains.'%');
            }
            else if (isset($req[$paramName]->greater_than)) {
                $users->where($paramName,'>',$req[$paramName]->greater_than);
            }
            else if (isset($req[$paramName]->less_than)) {
                $users->where($paramName,'<',$req[$paramName]->less_than);
            }
            else if (isset($req[$paramName]->equals_to)) {
                $users->where($paramName,'=',$req[$paramName]->equals_to);
            }
            else if (isset($req[$paramName]->equals_to)) {
                $users->where($paramName,'=',$req[$paramName]->equals_to);
            }
            else if (isset($req[$paramName]->from)) {
                $users->where($paramName,'>=',$req[$paramName]->from);
            }
            if (isset($req[$paramName]->to)) {
                $users->where($paramName,'<=',$req[$paramName]->to);
            }

        }
        $data = $users->get();
        return $data;

    }
    
}

