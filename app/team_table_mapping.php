<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\TableStructure;

class team_table_mapping extends Model {

    protected $table = 'team_table_mappings';
    protected $fillable = ['id', 'table_name', 'table_id', 'team_id','auth','socket_api'];
    public $timestamps = false;

    public function tableStructure() {
        return $this->hasMany(TableStructure::class, 'table_id', 'id');
    }

    public static function getUserTablesByTeam($teamIdArr) {
        $data = team_table_mapping::with('tableStructure.columnType')
                ->whereIn('team_id', $teamIdArr)
                ->get();
        return $data;
    }

    public static function makeNewTableEntry($paramArr) {
        $data = team_table_mapping::create($paramArr);
        return $data;
    }

    public static function getTableSourcesByTableIncrId($team_incr_id_arr) {
        $data = \DB::table('user_data_source')
                ->select('*')
                ->whereIn('table_incr_id', $team_incr_id_arr)
                ->get();
        return $data;
    }

    public static function getUserTablesNameById($tableId) {
        $data = team_table_mapping::with('tableStructure.columnType')
                ->where('id', $tableId)
                ->get();
        return $data;
    }

    public static function getTableByAuth($auth) {
        $data = team_table_mapping::with('tableStructure.columnType')
                ->wherein('auth', $auth)
                ->get();
        return $data;
    }

    public static function makeNewEntryForSource($table_incr_id, $dataSource) {
        $match_this = array('table_incr_id' => $table_incr_id, 'source' => $dataSource);
        $exists = \DB::table('user_data_source')->where($match_this)->get();
        $existsArr = json_decode(json_encode($exists), true);

        if ($existsArr) {
            echo "Working as required";
        } else {
            \DB::table('user_data_source')
                    ->insert(array('table_incr_id' => $table_incr_id, 'source' => $dataSource));
        }
        return True;
    }

    public static function makeNewEntryInTable($table_name, $input_param, $structure) {
        $data = 0;
        $unique_key = '';
        //print_r($structure);die;
        //$structure = json_decode($structureJson, TRUE);

        foreach ($input_param as $key => $value) {
            if ($structure[$key]['unique'] == 1) {
                $unique_key = $key;
                break;
            }
        }
        if (empty($unique_key) || empty($input_param[$key])) {
            return array('error' => 'unique_key_not_found');
        }
        $responseObj = \DB::table($table_name)
                ->select('*')
                ->where($unique_key, $input_param[$unique_key])
                ->get();


        $response = json_decode(json_encode($responseObj));

        if (empty($response)) {
            $data = \DB::table($table_name)
                    ->insert($input_param);
        } else {
            $update_data = array();
            foreach ($input_param as $key => $value) {
                if ($structure[$key]['type'] != 'airthmatic number') {
                    if (!empty($input_param[$key])) {
                        $update_data[$key] = $input_param[$key];
                    }
                } else {
                    if (!empty($input_param[$key])) {
                        $update_data[$key] = \DB::raw($key . ' + (' . $input_param[$key] . ')');
                    }
                }
            }

            $data = \DB::table($table_name)
                    ->where($unique_key, $input_param[$unique_key])
                    ->update($update_data);
        }
        $log_table = 'log' . substr($table_name, 4);
        \DB::table($log_table)
                ->insert($input_param);
        return array('success' => 'data_updated');
    }

    public static function updateTableStructure($paramArr) {
        $tableAutoIncId = $paramArr['id'];
        $socketApi = $paramArr['socketApi'];
        $data = \DB::table('team_table_mappings')
                ->where('id', $tableAutoIncId)
                ->update(['socket_api' => $socketApi]);
        return $data;
    }

    public static function updateTableData($paramArr) {
        $data = \DB::table($paramArr['table'])
                ->where($paramArr['where_key'], $paramArr['where_value'])
                ->update($paramArr['update']);
        return $data;
    }

}
