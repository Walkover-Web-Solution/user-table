<?php

namespace App\Http\Controllers;

use App\Classes\Utility;
use App\StoreTokens;
use App\Tabs;
use App\Tables;
use App\TabUsers;
use App\Users;
use App\team_table_mapping;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TableController extends Controller {

    public function loadSelectedTable($tableName) {
        $tableNameArr = team_table_mapping::getUserTablesNameById($tableName);
        $tableNameArr = json_decode(json_encode($tableNameArr), true);
        $userTableName = $tableNameArr[0]['table_name'];
        if (empty($tableNameArr[0]['table_id'])) {
            echo "no table found";
            exit();
        } else {
            $tableId = $tableNameArr[0]['table_id'];
            $tableId = "`$tableId`";
            $allTabs = \DB::table($tableId)
                    ->select('*')
                    ->get();
            $allTabs = json_decode(json_encode($allTabs), true);
            $data = Tabs::getTabsByTableId($tableId);
            $data = json_decode(json_encode($data), true);

            $filters = Tables::getFiltrableData($tableId);

            return view('home', array(
                'activeTab' => 'All',
                'tabs' => $data,
                'allTabs' => $allTabs,
                'tableId' => $tableName,
                'userTableName' => $userTableName,
                'filters' => $filters));
        }
    }

    public function loadSelectedTableFilterData($tableId, $tabName) {
        $tableNameArr = team_table_mapping::getUserTablesNameById($tableId);
        $tableNameArr = json_decode(json_encode($tableNameArr), true);
        $userTableName = $tableNameArr[0]['table_name'];
        if (empty($tableNameArr[0]['table_id'])) {
            echo "no table found";
            exit();
        } else {
            $tableIdMain = $tableNameArr[0]['table_id'];
            $data = Tabs::getTabsByTableId($tableIdMain);
            $data = json_decode(json_encode($data), true);
//            $alltabs = Tables::TabDataBySavedFilter($tableIdMain, $tabName);
            $tabData = Tables::TabDataBySavedFilter($tableIdMain, $tabName);
            $tabData = json_decode(json_encode($tabData), true);
            $filters = Tables::getFiltrableData($tableIdMain);

            return view('home', array(
                'activeTab' => $tabName,
                'tabs' => $data,
                'allTabs' => $tabData,
                'tableId' => $tableId,
                'userTableName' => $userTableName,
                'filters' => $filters));
        }
    }

    # function get search for selected filters

    public function applyFilters(Request $request) {
        $req = (array) ($request->filter);

        $tab = $request->tab;
        $tableId = $request->tableId;
        $tableNameArr = team_table_mapping::getUserTablesNameById($tableId);
        $tableNameArr = json_decode(json_encode($tableNameArr), true);
        $userTableName = $tableNameArr[0]['table_name'];
        if (empty($tableNameArr[0]['table_id'])) {
            echo "no table found";
            exit();
        } else {
            $data = $this->getAppliedFiltersData($req, $tableNameArr[0]['table_id']);
            $data = json_decode(json_encode($data), true);
            if (request()->wantsJson()) {
                return response(json_encode(array('body' => $data)), 200)->header('Content-Type', 'application/json');
            } else {
                return view('table.response', array(
                    'allTabs' => $data
                ));
            }
        }
    }

    public static function getAppliedFiltersData($req, $tableId) {
        $users = \DB::table($tableId)->selectRaw('*');

        foreach (array_keys($req) as $paramName) {

            if (isset($req[$paramName]['is'])) {
                $users->where($paramName, '=', $req[$paramName]['is']);
            } else if (isset($req[$paramName]['is_not'])) {
                $users->where($paramName, '<>', $req[$paramName]['is_not']);
            } else if (isset($req[$paramName]['contains'])) {
                $users->where($paramName, 'LIKE', '%' . $req[$paramName]['contains'] . '%');
            } else if (isset($req[$paramName]['not_contains'])) {
                $users->where($paramName, 'LIKE', '%' . $req[$paramName]['not_contains'] . '%');
            } else if (isset($req[$paramName]['greater_than'])) {
                $users->where($paramName, '>', $req[$paramName]['greater_than']);
            } else if (isset($req[$paramName]['less_than'])) {
                $users->where($paramName, '<', $req[$paramName]['less_than']);
            } else if (isset($req[$paramName]['equals_to'])) {
                $users->where($paramName, '=', $req[$paramName]['equals_to']);
            } else if (isset($req[$paramName]['equals_to'])) {
                $users->where($paramName, '=', $req[$paramName]['equals_to']);
            } else if (isset($req[$paramName]['from'])) {
                $users->where($paramName, '>=', $req[$paramName]['from']);
            }
            if (isset($req[$paramName]['to'])) {
                $users->where($paramName, '<=', $req[$paramName]['to']);
            }
        }
        $data = $users->get();

        return $data;
    }

}
