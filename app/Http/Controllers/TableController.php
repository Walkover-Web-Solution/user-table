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
use Illuminate\Database\QueryException;

class TableController extends Controller {

    public function __construct() {
        $this->middleware('auth');
    }

    public function loadSelectedTable($tableName) {
        $tableNameArr = team_table_mapping::getUserTablesNameById($tableName);
        $tableNameArr = json_decode(json_encode($tableNameArr), true);
        $userTableName = $tableNameArr[0]['table_name'];
        $userTableStructure = json_decode(json_decode(json_encode($tableNameArr[0]['table_structure']), true), TRUE);
        if (empty($tableNameArr[0]['table_id'])) {
            echo "no table found";
            exit();
        } else {
            $tableId = $tableNameArr[0]['table_id'];
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
                'filters' => $filters,
                'structure' => $userTableStructure));
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

    public function add(Request $request) {
        $input_data = $request->all();
        $table_auth = $request->header('Auth-Key');
        $response = team_table_mapping::getTableByAuth(array($table_auth));
        $response = json_decode(json_encode($response), true);
        if (empty($response)) {
            return response()->json(array('error' => 'authorization_failure'), 401);
        }
        $incoming_data = $request->all();
        $table_incr_id = $response[0]['id'];
        $dataSource = $incoming_data['socket_data_source'];
        unset($incoming_data['socket_data_source']);
        $table_name = $response[0]['table_id'];
        $table_structure = $response[0]['table_structure'];
        $response = team_table_mapping::makeNewEntryInTable($table_name, $incoming_data, $table_structure);
        if (isset($response['error'])) {
            return response()->json($response, 400);
        } else {
            team_table_mapping::makeNewEntryForSource($table_incr_id, $dataSource);
            return response()->json($response);
        }
    }

    public function loadSelectedTableStructure($tableName) {
        $tableNameArr = team_table_mapping::getUserTablesNameById($tableName);
        $tableNameArr = json_decode(json_encode($tableNameArr), true);
        return view('configureTable', array(
            'tableData' => $tableNameArr));
    }

    public function configureSelectedTable(Request $request) {
        $tableData = $request->input('tableData');
        if (empty($tableData)) {
            $arr['msg'] = "Nothing to added, Please add atleast one column";
            return response()->json($arr);
        }
        $tableId = $request->input('tableId');
        $tableNameArr = team_table_mapping::getUserTablesNameById($tableId);
        $tableNameArr = json_decode(json_encode($tableNameArr), true);

        $tableStructure = json_decode($tableNameArr[0]['table_structure'], TRUE);

        foreach ($tableData as $key => $value) {
            if (empty($value['name'])) {
                $arr['msg'] = "Name Can't be empty";
                return response()->json($arr);
            }
            if (empty($value['type'])) {
                $arr['msg'] = "type Can't be empty";
                return response()->json($arr);
            }
            $tableStructure[$value['name']] = array('type' => $value['type'], 'unique' => 'false', 'value' => $value['value']);
        }
        $tableStructure = json_encode($tableStructure);

        $tableName = $tableNameArr[0]['table_id'];
        $tableAutoIncId = $tableNameArr[0]['id'];
        $logTableName = "log_" . $tableNameArr[0]['table_name'] . "_" . $tableNameArr[0]['team_id'];


        if (Schema::hasTable($tableName)) {
            try {
                Schema::table($tableName, function (Blueprint $table) use ($tableData) {
                    foreach ($tableData as $key => $value) {
                        $table->string($value['name']);
                    }
                });
                Schema::table($logTableName, function (Blueprint $table) use ($tableData) {
                    foreach ($tableData as $key => $value) {
                        $table->string($value['name']);
                    }
                });
                $paramArr['id'] = $tableAutoIncId;
                $paramArr['table_structure'] = $tableStructure;
                $tableNameArr = team_table_mapping::updateTableStructure($paramArr);
            } catch (\Illuminate\Database\QueryException $ex) {
//                dd($ex->getMessage());
                $arr['msg'] = "Error in updation";
                return response()->json($arr);
            }

            $arr['msg'] = "Table Updated Successfuly";
            return response()->json($arr);
        } else {
            $arr['msg'] = "Table Not Found";
            return response()->json($arr);
        }
    }

    public function updateEntry(Request $request) {

        $update_details = $request->all();
        if (!isset($update_details['table_id'])) {
            return response()->json(array('error' => 'Invalid table id'));
        }
        $tableNameArr = team_table_mapping::getUserTablesNameById($update_details['table_id']);
        $tableNameArr = json_decode(json_encode($tableNameArr), true);

        $tableName = $tableNameArr[0]['table_id'];
        $param['table'] = $tableName;
        $param['where_key'] = 'id';
        $param['where_value'] = $update_details['row_id'];

        $param['update'] = array($update_details['coloumn_name'] => $update_details['new_value']);
        $response = team_table_mapping::updateTableData($param);
        if ($response == 1) {
            return response()->json(array('msg' => 'Data updated'));
        } else {
            return response()->json(array('msg' => 'Data couldnot be updated'));
        }
    }

}
