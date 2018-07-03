<?php

namespace App;

use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class team_table_mapping extends Model {

    protected $table = 'team_table_mappings';
    protected $fillable = ['id', 'table_name', 'table_id', 'team_id', 'table_structure', 'auth','socket_api', 'new_entry_api','parent_table_id'];
    public $timestamps = false;

    public function tableStructure() {
        return $this->hasMany(TableStructure::class, 'table_id', 'id')->orderBy('ordering','ASC');
    }

    public static function getUserTablesByTeam($teamIdArr) {
        $data = team_table_mapping::with('tableStructure.columnType')
                ->whereIn('team_id', $teamIdArr)
                ->get();
        return $data;
    }

    public static function getUserTablesByTeamAndTableId($teamIdArr,$tableId) {
        $data = team_table_mapping::with('tableStructure.columnType')
                ->whereIn('team_id', $teamIdArr)
                ->where('id', $tableId)
                ->get();
        return $data;
    }
    public static function getUserTablesByEmailAndTableId($email,$tableId) {
        $data = team_table_mapping::with('tableStructure.columnType')
                ->where('team_id', $email)
                ->where('id', $tableId)
                ->get();
        return $data;
    }
    

    public static function makeNewTableEntry($paramArr) {
        $data = team_table_mapping::create($paramArr);
        return $data;
    }

    public static function getTableSourcesByTableIncrId($team_incr_id_arr) {
        $data = DB::table('user_data_source')
                ->select('*')
                ->whereIn('table_incr_id', $team_incr_id_arr)
                ->get();
        return $data;
    }

    public static function getUserTablesNameById($tableId,$arrayAllowed=array()) {
        $data = team_table_mapping::with(['tableStructure'=> function ($query) use($arrayAllowed){
            if(!empty($arrayAllowed))
                $query->whereIn('id', $arrayAllowed);
        },'tableStructure.columnType'])->where('id', $tableId)->first()->toArray();
       
        return $data;
    }
    
    public static function getUserTablesColumnNameById($tableId) {
        $data = DB::table('table_structures')
            ->select('column_name', 'ordering')
            ->where('table_id', $tableId)
            ->where('display', 1)
            ->get();
        return json_decode(json_encode($data), true);
    }

    public static function getUserTablesNameByParentId($tableId) {
        $data = team_table_mapping::select('*')->where('parent_table_id', $tableId)
                ->get()->toArray();
        return $data;
    }

    public static function getUserTablesNameByEmail($email) {
        $data = team_table_mapping::select('*')->where('team_id', $email)
                ->get()->toArray();
        return $data;
    }

    public static function getUserTablesNameByName($tableName) {
        $data = team_table_mapping::with('tableStructure.columnType')
            ->where('table_name', $tableName)
            ->first()->toArray();
        return $data;
    }

    public static function getUserTableByTableId($tableId) {
        $data = team_table_mapping::select('*')->where('table_id', $tableId)
            ->first()->toArray();
        return $data;
    }

    public static function getDataById($id) {
        $data = DB::table('team_table_mappings')
                ->select('*')
                ->where('id', $id)
                ->get();
        return $data;
    }

    public static function getTableByAuth($auth) {
        $data = team_table_mapping::with('tableStructure.columnType')
                ->where('auth', $auth)
                ->first()->toArray();
        return $data;
    }

    public static function makeNewEntryForSource($table_incr_id, $dataSource) {
        $match_this = array('table_incr_id' => $table_incr_id, 'source' => $dataSource);
        $exists = DB::table('user_data_source')->where($match_this)->get();
        $existsArr = json_decode(json_encode($exists), true);

        if ($existsArr) {
            //echo "Working as required";
        } else {
            DB::table('user_data_source')
                    ->insert(array('table_incr_id' => $table_incr_id, 'source' => $dataSource));
        }
        return True;
    }

    public static function makeNewEntryInTable($table_name, $input_param, $structure) {

        $unique_key = '';
        $update_data = array();
        foreach ($input_param as $key => $value) {
           if(isset($structure[$key])) {
               if ($structure[$key]['unique'] == 1) {
                   $unique_key = $key;
               }
               if($structure[$key]['column_type_id']==9){
                   if (($timestamp = strtotime($value)) === false) {
                       $input_param[$key]  ='';
                   } else {
                       $input_param[$key] = $timestamp;
                   }
               }
           }
        }

        if (empty($unique_key)) {
            $unique_key = 'id';
        }else{
            if(isset($input_param['id'])){
                unset($input_param['id']);
            }
        }
        $table = DB::table($table_name);
        $old_data = $updatedData = array();
        if (!empty($input_param[$unique_key])) {
            $responseObj = $table->select('*')->where($unique_key, $input_param[$unique_key])->first();
            $old_data = json_decode(json_encode($responseObj),true);
        }
        
        if(!empty($old_data)) {
            foreach ($structure as $key => $column) {
                if (isset($input_param[$key])) {
                    if ($column['column_type_id'] != 4 && $column['column_type_id']!=10) {
                        if (!empty($input_param[$key])) {
                            if(is_array($input_param[$key]))
                                $update_data[$key] = json_encode($input_param[$key]);
                            else
                            $update_data[$key] = $input_param[$key];
                        }
                    } else {
                        // if ($column['column_type_id'] ==4 && !empty($input_param[$key])) {
                        //     $update_data[$key] = DB::raw($key . ' + (' . $input_param[$key] . ')');
                        // }
                        if ($column['column_type_id'] ==4 && !empty($input_param[$key])) {
                            if(isset($old_data) && $old_data[$key]==null)
                            {
                                $update_data[$key] = DB::raw($input_param[$key]);
                            }
                            else
                            {
                                if($old_data[$key]==1 && $input_param[$key]==1)
                                {
                                    $update_data[$key] = 2;
                                }
                                else
                                {
                                    $update_data[$key] = DB::raw($key . ' + ' . $input_param[$key]);
                                }
                            }
                        }
                        if($column['column_type_id'] == 10 && !empty($input_param[$key])){
                            if($input_param[$key] == 'me' && $loggedInUser = Auth::user())
                                $update_data[$key] = $loggedInUser->email;
                            else
                                $update_data[$key] = $input_param[$key];
                        }
                    }
                    if ($old_data[$key] != $input_param[$key]) {
                        $updatedData[$key] = $input_param[$key];
                    } else {
                        if($column['column_type_id'] !=4)
                        {
                            unset($update_data[$key]);
                            unset($old_data[$key]);
                        }
                        if($column['column_type_id']==4 && $input_param[$key]!=$old_data[$key] && $input_param[$key]!=1)
                        {
                            unset($update_data[$key]);
                            unset($old_data[$key]);
                        }
                    }
                } else {
                    unset($old_data[$key]);
                }
            }
            unset($old_data['id']);
            $message = 'Entry Updated';
            $action = '';
            if(!empty($update_data)) {
                $action = 'Update';
                $update_data['updated_at']=strtotime(now());
                $table->where($unique_key, $input_param[$unique_key])
                    ->update($update_data);
            }
            $update_data = $table->select('*')
                ->where($unique_key, $input_param[$unique_key])
                ->first();
        }else{
            $message = 'Entry Added';
            $action = 'Create';
            $input_param['created_at']=strtotime(now());
            $table->insert($input_param);
            $update_data = $table->select('*')->orderBy('id', 'DESC')->first();
        }
        return array('success' => $message, 'data' => $update_data, 'action' => $action, 'details' => $updatedData, 'old_data' => $old_data);
    }

    public static function updateTableStructure($paramArr, $tableId) {
        $data = DB::table('team_table_mappings')
                ->where('id', $tableId)
                ->update($paramArr);

        return $data;
    }

    public static function updateTableData($paramArr) {
        $data = DB::table($paramArr['table'])
                ->where($paramArr['where_key'], $paramArr['where_value'])
                ->update($paramArr['update']);
        return $data;
    }

    public static function updateTableStructureData($tableId, $structure) {
        $data = DB::table('team_table_mappings')
                ->where('id', $tableId)
                ->update(['table_structure' => $structure]);
        return $data;
    }

}
