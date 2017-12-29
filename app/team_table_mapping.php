<?php

namespace App;

use DB;
use Illuminate\Database\Eloquent\Model;

class team_table_mapping extends Model {

    protected $table = 'team_table_mappings';
    protected $fillable = ['id', 'table_name', 'table_id', 'team_id', 'table_structure', 'auth','socket_api', 'new_entry_api'];
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

    public static function getUserTablesNameById($tableId) {
        $data = team_table_mapping::with('tableStructure.columnType')
                ->where('id', $tableId)
                ->first()->toArray();
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

        if (empty($input_param[$unique_key])) {
            $response=array();
        } else {
            $responseObj = $table->select('*')->where($unique_key, $input_param[$unique_key])->first();
            $response = json_decode(json_encode($responseObj));
        }
        $updatedData = array();
        $old_data = array();
        if (empty($response)) {
            $message = 'Entry Added';
            $action = 'Create';
            $table->insert($input_param);
            $update_data = $table->select('*')->orderBy('id', 'DESC')->first();
        } else {
            $oldRow = $table->select('*')
                ->where($unique_key, $input_param[$unique_key])
                ->first();

            foreach ($oldRow as $k => $val) {
                $old_data[$k] = $val;
            }
            foreach ($input_param as $key => $value) {
                if(isset($structure[$key]))
                {
                    if ($structure[$key]['type'] != 'airthmatic number') {
                        if (!empty($input_param[$key])) {
                            $update_data[$key] = $input_param[$key];
                        }
                    } else {
                        if (!empty($input_param[$key])) {
                            $update_data[$key] = DB::raw($key . ' + (' . $input_param[$key] . ')');
                        }
                    }
                    if ($old_data[$key] != $input_param[$key]) {
                        $updatedData[$key] = $input_param[$key];
                    } else {
                        unset($old_data[$key]);
                    }
                } else if ($key == 'id') {
                    unset($old_data[$key]);
                }
            }
            $message = 'Entry Updated';
            $action = 'Update';
            $table->where($unique_key, $input_param[$unique_key])
                    ->update($update_data);
            $update_data = $table->select('*')
                ->where($unique_key, $input_param[$unique_key])
                ->first();
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
