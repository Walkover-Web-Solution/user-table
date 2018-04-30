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

    public static function TabDataBySavedFilter($tableId, $tabName,$pageSize)
    {
        if ($tabName == "All") {
            $data = DB::table($tableId)->selectRaw('*')->whereNull('is_deleted')->latest('id')->paginate($pageSize);
        } else {
            $tabSql = Tabs::where([['tab_name', $tabName], ['table_id', $tableId]])->first(['query']);
            $req = (array)json_decode($tabSql->query,true);
            $data = Tables::getFilteredUsersDetailsData($req, $tableId,$pageSize);
        }
        return $data;
    }

    /**
     * @param $req
     * @param $tableId
     * @param $pageSize
     * @return mixed
     */
    public static function getFilteredUsersDetailsData($req, $tableId, $pageSize)
    {
        $users = DB::table($tableId)->selectRaw('*');
        $coltypes = TableStructure::getTableColumnTypesArray($tableId);
        $columnArr = array();
        foreach($req as $k =>$r){
            $columnArr[$k]=$coltypes;
        }
        $usersNew = Tables::makeFilterQuery($req, $users,$columnArr,$tableId,'and');
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
            $tabSql = Tabs::where([['tab_name', $tabName], ['table_id', $tableId]])->first(['query','condition']);
            $req = (array)json_decode($tabSql->query,true);
            $condition = empty($tabSql->condition)?'and':$tabSql->condition;
            $count = Tables::getCountOfFilteredData($req, $tableId, $coltypes,$condition);
        }
        return $count;
    }

    public static function getCountOfFilteredData($req, $tableId, $coltypes,$condition)
    {
        $users = DB::table($tableId);
        $colArr = array();
        foreach($req as $k=>$r){
            $colArr[$k]=$coltypes;
        }
        $usersNew = Tables::makeFilterQuery($req, $users,$colArr,$tableId,$condition);
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

    public static function makeFilterQuery($reqs, $users,$coltypes,$tableName,$condition)
    {
        $users =self::getConditionQuery($reqs, $coltypes, $condition, $users, $tableName);
        
        if($users)
            return $users->whereNull('is_deleted');
        else
            return $users;
    }
    
    public static function getConditionQuery($reqs,$coltype,$condition,$users,$tableName){
        $flag=0;
        $errorFlag = 0;
        foreach ($reqs as $k => $req)
        {
            foreach (array_keys($req) as $paramName) {
                $colomntype = $coltype[$k][$paramName];
                if (!Schema::hasColumn($tableName, $paramName)) //check whether table has this column
                {
                    $errorFlag =1;
                    break;
                }
                if (isset($req[$paramName]['is'])) {
                    $val = $req[$paramName]['is'];
                    if($flag && $condition=='or')
                    {
                        if ($val == 'me' && $loggedInUser = Auth::user()) {
                            $users->orWhere($paramName, '=', $loggedInUser->email);
                        } else
                            $users->orWhere($paramName, '=', $req[$paramName]['is']);
                    }else{
                        if ($val == 'me' && $loggedInUser = Auth::user()) {
                            $users->where($paramName, '=', $loggedInUser->email);
                        } else
                            $users->where($paramName, '=', $req[$paramName]['is']);
                    }
                    $flag=1;
                }
                if (isset($req[$paramName]['is_not'])) {
                    if($flag && $condition=='or'){
                        $users->orWhere($paramName, '<>', $req[$paramName]['is_not']);
                    }else
                        $users->where($paramName, '<>', $req[$paramName]['is_not']);
                    $flag=1;
                }
                if (isset($req[$paramName]['starts_with'])) {
                    if($flag && $condition=='or'){
                        $users->orWhere($paramName, 'LIKE', '' . $req[$paramName]['starts_with'] . '%');
                    }else{
                        $users->where($paramName, 'LIKE', '' . $req[$paramName]['starts_with'] . '%');
                    }
                    $flag=1;
                }
                if (isset($req[$paramName]['ends_with'])) {
                    if($flag && $condition=='or'){
                        $users->orWhere($paramName, 'LIKE', '%' . $req[$paramName]['ends_with'] . '');
                    }
                    else
                        $users->where($paramName, 'LIKE', '%' . $req[$paramName]['ends_with'] . '');
                    $flag=1;
                } 
                if (isset($req[$paramName]['contains'])) {
                    if($flag && $condition=='or'){
                        $users->orWhere($paramName, 'LIKE', '%' . $req[$paramName]['contains'] . '%');
                    }else
                        $users->where($paramName, 'LIKE', '%' . $req[$paramName]['contains'] . '%');
                    $flag=1;
                }
                if (isset($req[$paramName]['not_contains'])) {
                    if($flag && $condition=='or'){
                        $users->orWhere($paramName, 'LIKE', '%' . $req[$paramName]['not_contains'] . '%');
                    }else
                        $users->where($paramName, 'LIKE', '%' . $req[$paramName]['not_contains'] . '%');
                    $flag=1;
                } 
                if (isset($req[$paramName]['is_unknown'])) {
                    if($flag && $condition=='or'){
                        $users->OrWhere(function ($query) use($paramName){
                            $query->orWhereNull($paramName)
                            ->orWhere($paramName, '');
                        });
                    }else{
                        $users->where(function ($query) use($paramName){
                            $query->whereNull($paramName)
                            ->orWhere($paramName, '');
                        });
                    }
                    $flag=1;
                } 
                if (isset($req[$paramName]['has_any_value'])) {
                    if($flag && $condition=='or'){
                        $users->OrWhere(function ($query) use($paramName){
                            $query->orWhereNotNull($paramName)
                            ->where($paramName, '<>', '');
                        });
                    }else{
                        $users->where(function ($query) use($paramName){
                            $query->whereNotNull($paramName)
                            ->where($paramName, '<>','');
                        });
                    }
                    $flag=1;
                }
                if (isset($req[$paramName]['greater_than'])) {
                    if($flag && $condition=='or'){
                        $users->orWhere($paramName, '>', $req[$paramName]['greater_than']);
                    }else
                        $users->where($paramName, '>', $req[$paramName]['greater_than']);
                    $flag=1;
                } 
                if (isset($req[$paramName]['less_than'])) {
                    if($flag && $condition=='or'){
                        $users->orWhere($paramName, '<', $req[$paramName]['less_than']);
                    }else
                        $users->where($paramName, '<', $req[$paramName]['less_than']);
                    $flag=1;
                } 
                if (isset($req[$paramName]['equals_to'])) {
                    if($flag && $condition=='or'){
                        $users->orWhere($paramName, '=', $req[$paramName]['equals_to']);
                    }else
                        $users->where($paramName, '=', $req[$paramName]['equals_to']);
                    $flag=1;
                } 
                if (isset($req[$paramName]['equals_to'])) {
                    if($flag && $condition=='or'){
                        $users->orWhere($paramName, '=', $req[$paramName]['equals_to']);
                    }else
                        $users->where($paramName, '=', $req[$paramName]['equals_to']);
                    $flag=1;
                } 
                if (isset($req[$paramName]['from'])) {
                    if($flag && $condition=='or'){
                        $users->orWhere($paramName, '>=', $req[$paramName]['from']);
                    }else
                        $users->where($paramName, '>=', $req[$paramName]['from']);
                    $flag=1;
                } 
                if (isset($req[$paramName]['to'])) {
                    if($flag && $condition=='or'){
                        $users->orWhere($paramName, '<=', $req[$paramName]['to']);
                    }else
                        $users->where($paramName, '<=', $req[$paramName]['to']);
                    $flag=1;
                } 
                if (isset($req[$paramName]['on'])) {
                    $d = $req[$paramName]['on'];
                    $st = Carbon::createFromFormat('Y-m-d', $d)->startOfDay()->toDateTimeString();
                    $enddt = Carbon::createFromFormat('Y-m-d', $d)->endOfDay()->toDateTimeString();
                    $sttimestamp = strtotime($st);
                    $endtimestamp = strtotime($enddt);
                    if($flag && $condition=='or'){
                        $users->orWhere($paramName, '>=', $sttimestamp)->where($paramName, '<=', $endtimestamp);
                    }else
                        $users->where($paramName, '>=', $sttimestamp)->where($paramName, '<=', $endtimestamp);
                    $flag=1;
                }
                if (isset($req[$paramName]['before'])) {
                    if ($colomntype == 'date') {
                        $timestamp = strtotime($req[$paramName]['before']);
                        if($flag && $condition=='or'){
                            $users->orWhere($paramName, '<=', $timestamp)->where($paramName, '>', 0);
                        }else
                            $users->where($paramName, '<=', $timestamp)->where($paramName, '>', 0);
                    } else {
                        if($flag && $condition=='or'){
                            $users->orWhere($paramName, '<=', $req[$paramName]['before']);
                        } else {
                            $users->where($paramName, '<=', $req[$paramName]['before']);
                        }
                    }
                    $flag=1;
                } 
                if (isset($req[$paramName]['after'])) {
                    if ($colomntype == 'date') {
                        $timestamp = strtotime($req[$paramName]['after']);
                        if($flag && $condition=='or'){
                            $users->orWhere($paramName, '>=', $timestamp);
                        }else
                            $users->where($paramName, '>=', $timestamp);
                    } else {
                        if($flag && $condition=='or'){
                            $users->orWhere($paramName, '>=', $req[$paramName]['after']);
                        }else
                            $users->where($paramName, '>=', $req[$paramName]['after']);
                    }
                    $flag=1;
                } 
                if (isset($req[$paramName]['days_before'])) {
                    $days = $req[$paramName]['days_before'];
                    $daysbefore = time() - ($days * 24 * 60 * 60);
                    if($flag && $condition=='or'){
                        $users->orWhere($paramName, '<=', $daysbefore)->where($paramName, '>', 0);
                    }else{
                       $users->where($paramName, '<=', $daysbefore)->where($paramName, '>', 0); 
                    }
                    $flag=1;
                } 
                if (isset($req[$paramName]['days_after'])) {
                    $days = $req[$paramName]['days_after'];
                    $daysafter = time() + ($days * 24 * 60 * 60);
                    if($flag && $condition=='or'){
                        $users->orWhere($paramName, '>=', $daysafter);
                    }else{
                        $users->where($paramName, '>=', $daysafter);
                    }
                    $flag=1;
                }
            }
        }
        if($errorFlag){
            return false;
        }
        return $users;
    }

}