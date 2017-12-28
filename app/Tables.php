<?php

namespace App;

use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class Tables extends Model
{
    public static function getSQLData($sql)
    {
        $tableData = DB::select($sql);
        return $tableData;
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
            }

        }
        return $data;
    }

    public static function TabDataBySavedFilter($tableId, $tabName,$pageSize)
    {
        if ($tabName == "All") {
            $data = DB::table($tableId)->selectRaw('*')->latest('id')->paginate($pageSize);
        } else {
            $tabSql = Tabs::where([['tab_name', $tabName], ['table_id', $tableId]])->first(['query']);
            $req = (array)json_decode($tabSql->query);
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
        $users = Tables::makeFilterQuery($req, $users);
        return $users->latest('id')->paginate($pageSize);
    }

    public static function createMainTable($tableName, $data)
    {
        Schema::create($tableName, function (Blueprint $table) use ($data) {
            $table->increments('id');
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';
            foreach ($data as $value) {
                $value['name'] = strtolower(preg_replace('/\s+/', '_', $value['name']));
                if ($value['unique'] == 'true') {
                    $table->string($value['name'])->unique($value['name']);
                } else {
                    if ($value['type'] == 9) {
                        $table->integer($value['name'])->unsigned()->nullable();
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

    public static function getCountOfTabsData($tableId, $tabName)
    {
        if ($tabName == "All") {
            $count = DB::table($tableId)->count();
        } else {
            $tabSql = Tabs::where([['tab_name', $tabName], ['table_id', $tableId]])->first(['query']);
            $req = (array)json_decode($tabSql->query);
            $count = Tables::getCountOfFilteredData($req, $tableId);
        }
        return $count;
    }

    public static function getCountOfFilteredData($req, $tableId)
    {
        $users = DB::table($tableId);
        $users = Tables::makeFilterQuery($req, $users);
        $count = $users->count();
        return $count;
    }

    public static function getAllTabsCount($tableId, $tabs)
    {
        $arrTabCount = array();
        if (!empty($tabs)) {
            foreach ($tabs as $val) {
                $tab_name = $val['tab_name'];
                $tabCount = Tables::getCountOfTabsData($tableId, $tab_name);
                $arrTabCount[] = array($tab_name => $tabCount);
            }
        }
        return $arrTabCount;
    }

    public static function makeFilterQuery($req, $users)
    {
        foreach (array_keys($req) as $paramName) {

            if (isset($req[$paramName]->is)) {
                $val = $req[$paramName]->is;
                if ($val == 'me' && $loggedInUser = Auth::user())
                    $users->where($paramName, '=', $loggedInUser->email);
                else
                    $users->where($paramName, '=', $req[$paramName]->is);
            } else if (isset($req[$paramName]->is_not)) {
                $users->where($paramName, '!=', $req[$paramName]->is_not);
            } else if (isset($req[$paramName]->contains)) {
                $users->where($paramName, 'LIKE', '%' . $req[$paramName]->contains . '%');
            } else if (isset($req[$paramName]->not_contains)) {
                $users->where($paramName, 'NOT LIKE', '%' . $req[$paramName]->not_contains . '%');
            } else if (isset($req[$paramName]->starts_with)) {
                $users->where($paramName, 'LIKE', '' . $req[$paramName]->starts_with . '%');
            } else if (isset($req[$paramName]->ends_with)) {
                $users->where($paramName, 'LIKE', '%' . $req[$paramName]->ends_with . '');
            } else if (isset($req[$paramName]->is_unknown)) {
                $users->whereNull($paramName)->orWhere($paramName, '');
            } else if (isset($req[$paramName]->has_any_value)) {
                $users->whereNotNull($paramName)->where($paramName, '<>', '');
            } else if (isset($req[$paramName]->greater_than)) {
                $users->where($paramName, '>', $req[$paramName]->greater_than);
            } else if (isset($req[$paramName]->less_than)) {
                $users->where($paramName, '<', $req[$paramName]->less_than);
            } else if (isset($req[$paramName]->equals_to)) {
                $users->where($paramName, '=', $req[$paramName]->equals_to);
            } else if (isset($req[$paramName]->equals_to)) {
                $users->where($paramName, '=', $req[$paramName]->equals_to);
            } else if (isset($req[$paramName]->from)) {
                $users->where($paramName, '>=', $req[$paramName]->from);
            }
            if (isset($req[$paramName]->to)) {
                $users->where($paramName, '<=', $req[$paramName]->to);
            }
        }
        return $users;
    }

}