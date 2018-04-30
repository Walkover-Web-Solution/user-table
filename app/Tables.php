<?php

namespace App;

use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class Tables extends Model
{
    public static function getSQLData($sql)
    {
        $tableData = DB::select($sql);
        return $tableData;
    }

    public static function markRecordsAsDeleted($tableId, $records){
        DB::table($tableId)->whereIn('id',$records)->update(['is_deleted' => 1]);
    }
    public static function getFiltrableData($tableId, $userTableStructure, $teammates)
    {
        $forStr = array('is' => null,
            'is_not' => null,
            'starts_with' => null,
            'ends_with' => null,
            'contains' => null,
            'not_contains' => null,
            'is_unknown' => null,
            'has_any_value' => null
        );
        $forInt = array('less_than' => null,
            'greater_than' => null,
            'is' => null,
            'is_not' => null,
            'equals_to' => null,
            'is_unknown' => null,
            'has_any_value' => null
        );
        $forDate = array('Relative' => 'group',
            'days_after' => null,
            'days_before' => null,
            'Absolute' => 'group',
            'after' => null,
            'on' => null,
            'before' => null,
            'is_unknown' => null,
            'has_any_value' => null
        );
        $forDropDown = array('is' => null,
            'is_not' => null,
            'is_unknown' => null,
            'has_any_value' => null
        );
        $forTeamMates = array('is' => null,
            'is_not' => null,
            'is_unknown' => null,
            'has_any_value' => null
        );
        $forLongText = array(
            'contains' => null,
            'is_unknown' => null,
            'has_any_value' => null
        );
        $data = array();

        foreach ($userTableStructure as $column => $struct) {
            $col_name = $column;
            $col_type = $struct["type"];
            $col_options = $struct["value_arr"]["options"];

            if ($col_type == 'text' || $col_type == 'email' || $col_type == 'phone') {
                $col_detail = array();
                $col_detail['col_name'] = $col_name;
                $col_detail['col_type'] = $col_type;
                $col_detail['col_filter'] = $forStr;
                $col_detail['col_options'] = $col_options;
                $data[$col_name] = $col_detail;
            } else if ($col_type == 'any number' || $col_type == 'airthmatic number') {
                $col_detail = array();
                $col_detail['col_name'] = $col_name;
                $col_detail['col_type'] = $col_type;
                $col_detail['col_filter'] = $forInt;
                $col_detail['col_options'] = $col_options;
                $data[$col_name] = $col_detail;
            } else if ($col_type == 'date') {
                $col_detail = array();
                $col_detail['col_name'] = $col_name;
                $col_detail['col_type'] = $col_type;
                $col_detail['col_filter'] = $forDate;
                $col_detail['col_options'] = $col_options;
                $data[$col_name] = $col_detail;
            } else if ($col_type == 'dropdown') {
                $col_detail = array();
                $col_detail['col_name'] = $col_name;
                $col_detail['col_type'] = $col_type;
                $col_detail['col_filter'] = $forDropDown;
                $col_detail['col_options'] = $col_options;
                $data[$col_name] = $col_detail;
            } else if ($col_type == 'my teammates') {
                $col_detail = array();
                $col_detail['col_name'] = $col_name;
                $col_detail['col_type'] = $col_type;
                $col_detail['col_filter'] = $forTeamMates;
                $col_detail['col_options'] = $teammates;
                $data[$col_name] = $col_detail;
            }else if($col_type == 'long text'){
                $col_detail = array();
                $col_detail['col_name'] = $col_name;
                $col_detail['col_type'] = $col_type;
                $col_detail['col_filter'] = $forLongText;
                $col_detail['col_options'] = $col_options;
                $data[$col_name] = $col_detail;
            }

        }
        return $data;
    }

    public static function TabDataBySavedFilter($tableId, $tabName,$pageSize, $tabcondition)
    {
        if ($tabName == "All") {
            $data = DB::table($tableId)->selectRaw('*')->whereNull('is_deleted')->latest('id')->paginate($pageSize);
        } else {
            $tabSql = Tabs::where([['tab_name', $tabName], ['table_id', $tableId]])->first(['query']);
            $req = (array)json_decode($tabSql->query,true);
            $data = Tables::getFilteredUsersDetailsData($req, $tableId,$pageSize, $tabcondition);
        }
        return $data;
    }

    /**
     * @param $req
     * @param $tableId
     * @param $pageSize
     * @return mixed
     */
    public static function getFilteredUsersDetailsData($req, $tableId, $pageSize, $tabcondition)
    {
        $users = DB::table($tableId)->selectRaw('*');
        $coltypes = TableStructure::getTableColumnTypesArray($tableId);
        $usersNew = Tables::makeFilterQuery($req, $users,$coltypes,$tableId, $tabcondition);
        if($usersNew)
            return $usersNew->latest('id')->paginate($pageSize);
        else {
            return $users->latest('id')->paginate($pageSize);
        }
    }

    public static function createMainTable($tableName, $data)
    {
        Schema::create($tableName, function (Blueprint $table) use ($data) {
            $table->increments('id');
            $table->string('is_deleted')->nullable();
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';
            foreach ($data as $value) {
                $value['name'] = strtolower(preg_replace('/\s+/', '_', $value['name']));
                if ($value['unique'] == 'true') {
                    $table->string($value['name'])->unique($value['name']);
                } else {
                    if ($value['type'] == 9) {
                        $table->integer($value['name'])->unsigned()->nullable();
                    }else if($value['type'] == 11){
                        $table->longText($value['name'])->nullable();
                    }else if($value['type']==4){
                        $table->float($value['name'], 15, 2)->default(0);
                    } else {
                        $table->string($value['name'])->nullable();
                    }
                }
            }
        });
    }

    public static function createLogTable($logTableName)
    {
        Schema::create($logTableName, function (Blueprint $table) {
            $table->increments('id');
            $table->string('user_id')->nullable();
            $table->string('content_type', 72)->nullable();
            $table->integer('content_id')->nullable();
            $table->string('action', 32)->nullable();
            $table->string('description')->nullable();
            $table->text('details')->nullable();
            $table->text('old_data')->nullable();
            $table->string('ip_address', 64);
            $table->string('user_agent');
            $table->nullableTimestamps();
        });
    }

    public static function getCountOfTabsData($tableId, $tabName,$coltypes)
    {
        if ($tabName == "All") {
            $count = DB::table($tableId)->count();
        } else {
            $tabSql = Tabs::where([['tab_name', $tabName], ['table_id', $tableId]])->first(['query']);
            $req = (array)json_decode($tabSql->query,true);
            $tabcondition = isset($tabSql['condition']) && !empty($tabSql['condition']) ? $tabSql['condition'] : 'and';
            $count = Tables::getCountOfFilteredData($req, $tableId, $coltypes, $tabcondition);
        }
        return $count;
    }

    public static function getCountOfFilteredData($req, $tableId, $coltypes, $tabcondition)
    {
        $users = DB::table($tableId);
        $usersNew = Tables::makeFilterQuery($req, $users,$coltypes,$tableId, $tabcondition);
        if($usersNew)
            $count = $usersNew->count();
        else {
            $count = $users->count();
        }
        return $count;
    }

    public static function getAllTabsCount($tableId, $tabs)
    {
        $arrTabCount = array();
        $coltypes = TableStructure::getTableColumnTypesArray($tableId);
        if (!empty($tabs)) {
            foreach ($tabs as $val) {
                $tab_name = $val['tab_name'];
                $tabCount = Tables::getCountOfTabsData($tableId, $tab_name, $coltypes);
                $arrTabCount[] = array($tab_name => $tabCount);
            }
        }
        return $arrTabCount;
    }

    public static function makeFilterQuery($reqs, $users,$coltypes,$tableName, $tabcondition = 'and')
    {
        $errorFlag = 0;
        foreach($reqs as $req)
        {
        foreach (array_keys($req) as $paramName) {
            $colomntype = isset($coltypes[$paramName])?$coltypes[$paramName]:'';
            if (!Schema::hasColumn($tableName, $paramName)) //check whether table has this column
            {
                $errorFlag =1;
                break;
            }
            if (isset($req[$paramName]['is'])) {
                $val = $req[$paramName]['is'];
                if($tabcondition == 'or')
                {
                    if ($val == 'me' && $loggedInUser = Auth::user()) {
                        $users->orWhere($paramName, '=', $loggedInUser->email);
                    } else
                        $users->where($paramName, '=', $req[$paramName]['is']);
                }
                else
                {
                    if ($val == 'me' && $loggedInUser = Auth::user()) {
                        $users->where($paramName, '=', $loggedInUser->email);
                    } else
                        $users->where($paramName, '=', $req[$paramName]['is']);
                }
            } else if (isset($req[$paramName]['is_not'])) {
                if($tabcondition == 'or')
                    $users->orWhere($paramName, '<>', $req[$paramName]['is_not']);
                else
                    $users->where($paramName, '<>', $req[$paramName]['is_not']);
            } else if (isset($req[$paramName]['starts_with'])) {
                if($tabcondition == 'or')
                    $users->orWhere($paramName, 'LIKE', '' . $req[$paramName]['starts_with'] . '%');
                else
                    $users->where($paramName, 'LIKE', '' . $req[$paramName]['starts_with'] . '%');
            } else if (isset($req[$paramName]['ends_with'])) {
                if($tabcondition == 'or')
                    $users->orWhere($paramName, 'LIKE', '%' . $req[$paramName]['ends_with'] . '');
                else
                    $users->where($paramName, 'LIKE', '%' . $req[$paramName]['ends_with'] . '');
            } else if (isset($req[$paramName]['contains'])) {
                if($tabcondition == 'or')
                    $users->orWhere($paramName, 'LIKE', '%' . $req[$paramName]['contains'] . '%');
                else
                    $users->where($paramName, 'LIKE', '%' . $req[$paramName]['contains'] . '%');
            } else if (isset($req[$paramName]['not_contains'])) {
                if($tabcondition == 'or')
                    $users->orWhere($paramName, 'LIKE', '%' . $req[$paramName]['not_contains'] . '%');
                else
                    $users->where($paramName, 'LIKE', '%' . $req[$paramName]['not_contains'] . '%');
            } else if (isset($req[$paramName]['is_unknown'])) {
                $users->whereNull($paramName)->orWhere($paramName, '');
            } else if (isset($req[$paramName]['has_any_value'])) {
                if($tabcondition == 'or')
                    $users->orWhereNotNull($paramName)->where($paramName, '<>', '');
                else
                    $users->whereNotNull($paramName)->where($paramName, '<>', '');
            } else if (isset($req[$paramName]['greater_than'])) {
                if($tabcondition == 'or')
                    $users->orWhere($paramName, '>', $req[$paramName]['greater_than']);
                else
                    $users->where($paramName, '>', $req[$paramName]['greater_than']);
            } else if (isset($req[$paramName]['less_than'])) {
                if($tabcondition == 'or')
                    $users->orWhere($paramName, '<', $req[$paramName]['less_than']);
                else
                    $users->where($paramName, '<', $req[$paramName]['less_than']);
            } else if (isset($req[$paramName]['equals_to'])) {
                if($tabcondition == 'or')
                    $users->orWhere($paramName, '=', $req[$paramName]['equals_to']);
                else
                    $users->where($paramName, '=', $req[$paramName]['equals_to']);
            } else if (isset($req[$paramName]['equals_to'])) {
                if($tabcondition == 'or')
                    $users->orWhere($paramName, '=', $req[$paramName]['equals_to']);
                else
                    $users->where($paramName, '=', $req[$paramName]['equals_to']);
            } else if (isset($req[$paramName]['from'])) {
                if($tabcondition == 'or')
                    $users->orWhere($paramName, '>=', $req[$paramName]['from']);
                else
                    $users->where($paramName, '>=', $req[$paramName]['from']);
            } else if (isset($req[$paramName]['to'])) {
                if($tabcondition == 'or')
                    $users->orWhere($paramName, '<=', $req[$paramName]['to']);
                else
                    $users->where($paramName, '<=', $req[$paramName]['to']);
            } else if (isset($req[$paramName]['on'])) {
               $d = $req[$paramName]['on'];
               $st = Carbon::createFromFormat('Y-m-d', $d)->startOfDay()->toDateTimeString();
               $enddt = Carbon::createFromFormat('Y-m-d', $d)->endOfDay()->toDateTimeString();
               $sttimestamp = strtotime($st);
               $endtimestamp = strtotime($enddt);
               if($tabcondition == 'or')
                    $users->orWhere($paramName, '>=', $sttimestamp)->where($paramName, '<=', $endtimestamp);
               else
                    $users->where($paramName, '>=', $sttimestamp)->where($paramName, '<=', $endtimestamp);
            } else if (isset($req[$paramName]['before'])) {
                if ($colomntype == 'date') {
                    $timestamp = strtotime($req[$paramName]['before']);
                    if($tabcondition == 'or')
                        $users->orWhere($paramName, '<=', $timestamp)->where($paramName, '>', 0);
                    else
                        $users->where($paramName, '<=', $timestamp)->where($paramName, '>', 0);
                } else {
                    if($tabcondition == 'or')
                        $users->orWhere($paramName, '<=', $req[$paramName]['before']);
                    else
                        $users->where($paramName, '<=', $req[$paramName]['before']);
                }
            } else if (isset($req[$paramName]['after'])) {
                if ($colomntype == 'date') {
                    $timestamp = strtotime($req[$paramName]['after']);
                    if($tabcondition == 'or')
                        $users->orWhere($paramName, '>=', $timestamp);
                    else
                        $users->where($paramName, '>=', $timestamp);
                } else {
                    if($tabcondition == 'or')
                        $users->orWhere($paramName, '>=', $req[$paramName]['after']);
                    else
                        $users->where($paramName, '>=', $req[$paramName]['after']);
                }
            } else if (isset($req[$paramName]['days_before'])) {
                $days = $req[$paramName]['days_before'];
                $daysbefore = time() - ($days * 24 * 60 * 60);
                if($tabcondition == 'or')
                    $users->orWhere($paramName, '<=', $daysbefore)->where($paramName, '>', 0);
                else
                    $users->where($paramName, '<=', $daysbefore)->where($paramName, '>', 0);
            } else if (isset($req[$paramName]['days_after'])) {
                $days = $req[$paramName]['days_after'];
                $daysafter = time() + ($days * 24 * 60 * 60);
                if($tabcondition == 'or')
                    $users->orWhere($paramName, '>=', $daysafter);
                else
                    $users->where($paramName, '>=', $daysafter);
            }

        }
        } 
        if($errorFlag){
            return false;
        }
        return $users->whereNull('is_deleted');
    }

}