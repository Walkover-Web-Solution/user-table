<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class Tables extends Model
{
    public static function getSQLData($sql)
    {
        $tableData = \DB::select($sql);
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
        $forDate = array('from' => null,
            'to' => null,
            'before' => null,
            'after' => null,
            'exactly' => null,
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
            'has_any_value' => null,
            'me' => null
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

    public static function TabDataBySavedFilter($tableId, $tabName)
    {
        if ($tabName == "All") {
            $data = \DB::table($tableId)->selectRaw('*')->limit(100)->get();
        } else {
            $tabSql = Tabs::where([['tab_name', $tabName], ['table_id', $tableId]])->first(['query']);
            $req = (array)json_decode($tabSql->query);
            $data = Tables::getFilteredUsersDetailsData($req, $tableId);
        }
        return $data;
    }


    public static function getFilteredUsersDetailsData($req, $tableId)
    {

        $users = \DB::table($tableId)->selectRaw('*');
        foreach (array_keys($req) as $paramName) {

            if (isset($req[$paramName]->me)) {
                $users->where($paramName, '=', Auth::user()->email);
            }

            if (isset($req[$paramName]->is)) {
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
        $data = $users->limit(100)->get();
        return $data;
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

    public static function createLogTable($logTableName, $data)
    {
        Schema::create($logTableName, function (Blueprint $table) use ($data) {
            $table->increments('id');
            foreach ($data as $value) {
                $value['name'] = strtolower(preg_replace('/\s+/', '_', $value['name']));
                $table->string($value['name'])->nullable();
            }
        });
    }

}