<?php

namespace App\Http\Controllers;

use App\Tabs;
use App\Tables;
use App\team_table_mapping;
use Illuminate\Http\Request;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\TableStructure;

class TableController extends Controller {

    public function createTable(Request $request) {
        $randomAuth = str_random(15);
        $data1 = $request->input('tableData');
        $data = $this->aasort($data1, "order"); // Array sort by abhishek jain

        $structureDataAr = array();
        foreach ($data as $key => $value) {
            $structureDataAr[$value['name']] = array('type' => $value['type'], 'unique' => $value['unique'], 'value' => $value['value']);
        }

        $structureDataJson = json_encode($structureDataAr);
        $userTableName = $request->input('tableName');
        $teamId = $request->input('teamId');

        $socketApi = $request->input('socketApi');
        $tableName = "main_".$userTableName.'_'.$teamId;
        $logTableName = "log_".$userTableName.'_'.$teamId;

        $tableData = '';
        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) use ($data) {
                $table->increments('id');
                $table->charset = 'utf8';
                $table->collation = 'utf8_unicode_ci';
                foreach ($data as $key => $value) {
                    $table->string($value['name']);
                    if ($value['unique'] == 'true') {
                        $table->unique($value['name']);
                    }
                }
            });
            Schema::create($logTableName, function (Blueprint $table) use ($data) {
                $table->increments('id');
                foreach ($data as $key => $value) {
                    $table->string($value['name']);
                }
            });

            $arr['msg'] = "Table Successfully created";
            // Make entry of table in team table mapping & store table structure
            $paramArr['table_name'] = $userTableName;
            $paramArr['table_id'] = $tableName;
            $paramArr['team_id'] = $teamId;
            $paramArr['table_structure'] = $structureDataJson;
            $paramArr['auth'] = $randomAuth;
            $paramArr['socket_api'] = $socketApi;
            $response = team_table_mapping::makeNewTableEntry($paramArr);

            #insert table structure in table
            return response()->json($arr);
        } else {
            $arr['msg'] = "Table already exists. Please use different table name";
            return response()->json($arr);
        }
    }

    public function getUserAllTables() {
        $teams = session()->get('team_array');
        $teamIdArr = array();
        $teamNameArr = array();

        foreach ($teams as $teamId => $teamName) {
            $teamNameArr[] = $teamName;
            $teamIdArr[] = $teamId;
        }
        session()->put('teamNames', $teamNameArr);
        session()->put('teams', $teams);

        $tableLst = $this->getUserTablesByTeamId($teamIdArr);
        $table_incr_id_arr = array();

        foreach ($tableLst as $key => $value) {
            $table_incr_id_arr[] = $value['id'];
        }
        $data = json_decode(json_encode(team_table_mapping::getTableSourcesByTableIncrId($table_incr_id_arr)), true);

        $source_arr = array();
        foreach ($data as $key => $value) {
            $source_arr[$value['table_incr_id']][] = $value['source'];
        }

        return view('showTable', array(
            'allTables' => $tableLst,
            'teamsArr' => $teams,
            'source_arr' => $source_arr
        ));
    } 

    public function getAllTablesForSocket(Request $request) {

        $team_ids = $request->input('team_ids');
        $team_id_array = explode(',', $team_ids);
        $table_data = $this->getUserTablesByTeamId($team_id_array);
        $table_array = array();
        $count = 0;
        foreach ($table_data as $key => $value) {
            $table_array[$value['team_id']][$count]['table_id'] = $value['table_id'];
            $table_array[$value['team_id']][$count]['table_name'] = $value['table_name'];
            $table_array[$value['team_id']][$count]['structure'] = $value['table_structure'];
            $table_array[$value['team_id']][$count]['auth'] = $value['auth'];
            $count++;
        }
        $response_arr = array();
        $count = 0;
        foreach ($table_array as $team_id => $table_data) {
            $response_arr[$count]['team_id'] = $team_id;
            $response_arr[$count]['tables'] = $table_data;
            $count++;
        }
        return response()->json($response_arr);
    }

    function getUserTablesByTeamId($teamIdArr) {
        $tableLst = team_table_mapping::getUserTablesByTeam($teamIdArr);
        $tableLstJson = json_decode(json_encode($tableLst), true);
        return $tableLstJson;
    }

    public function loadSelectedTable($tableName) {
        $tableNames = team_table_mapping::getUserTablesNameById($tableName);
        $tableNameArr = json_decode(json_encode($tableNames), true);
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
            $allTabsData = json_decode(json_encode($allTabs), true);
            $data = Tabs::getTabsByTableId($tableId);
            $tabs = json_decode(json_encode($data), true);

            $filters = Tables::getFiltrableData($tableId);
            if (!empty($tabs)) {
                foreach ($tabs as $val) {
                    $tab_name = $val['tab_name'];
                    $tabCountData = Tables::TabDataBySavedFilter($tableId, $tab_name);
                    $tabCount = count($tabCountData);

                    $arrTabCount[] = array($tab_name => $tabCount);
                }
            } else {
                $arrTabCount = array();
            }
            $allTabCount = count($allTabsData);

            return view('home', array(
                'activeTab' => 'All',
                'tabs' => $tabs,
                'allTabs' => $allTabsData,
                'allTabCount' => $allTabCount,
                'arrTabCount' => $arrTabCount,
                'tableId' => $tableName,
                'userTableName' => $userTableName,
                'filters' => $filters,
                'structure' => $userTableStructure));
        }
    }

    public function loadSelectedTableFilterData($tableId, $tabName) {
        $tableNames = team_table_mapping::getUserTablesNameById($tableId);
        $tableNameArr = json_decode(json_encode($tableNames), true);
        $userTableName = $tableNameArr[0]['table_name'];
        if (empty($tableNameArr[0]['table_id'])) {
            echo "no table found";
            exit();
        } else {
            $tableIdMain = $tableNameArr[0]['table_id'];
            $allTabs = \DB::table($tableIdMain)
                    ->select('*')
                    ->get();
            $allTabsData = json_decode(json_encode($allTabs), true);
            $data = Tabs::getTabsByTableId($tableIdMain);
            $tabs = json_decode(json_encode($data), true);
            if ($tabName == "All") {
                $tabArray = array();
            } else {
                $tabSql = Tabs::where([['tab_name', $tabName], ['table_id', $tableIdMain]])->first(['query'])->toArray();
                $tabArray = json_decode($tabSql['query'], true);
            }

            $tabDataJson = Tables::TabDataBySavedFilter($tableIdMain, $tabName);
            $tabData = json_decode(json_encode($tabDataJson), true);
            $filters = Tables::getFiltrableData($tableIdMain);
            if (!empty($tabs)) {
                foreach ($tabs as $val) {
                    $tab_name = $val['tab_name'];
                    $tabCountData = Tables::TabDataBySavedFilter($tableIdMain, $tab_name);
                    $tabCount = count($tabCountData);

                    $arrTabCount[] = array($tab_name => $tabCount);
                }
            } else {
                $arrTabCount = array();
            }
            $allTabCount = count($allTabsData);
            return view('home', array(
                'activeTab' => $tabName,
                'tabs' => $tabs,
                'allTabs' => $tabData,
                'allTabCount' => $allTabCount,
                'arrTabCount' => $arrTabCount,
                'tableId' => $tableId,
                'userTableName' => $userTableName,
                'filters' => $filters,
                'activeTabFilter' => $tabArray));
        }
    }

    # function get search for selected filters

    public function applyFilters(Request $request) {
        $req = (array) ($request->filter);

        $tableId = $request->tableId;
        $tableNames = team_table_mapping::getUserTablesNameById($tableId);
        $tableNameArr = json_decode(json_encode($tableNames), true);
        //$userTableName = $tableNameArr[0]['table_name'];
        if (empty($tableNameArr[0]['table_id'])) {
            echo "no table found";
            exit();
        } else {
            $jsonData = $this->getAppliedFiltersData($req, $tableNameArr[0]['table_id']);
            $data = json_decode(json_encode($jsonData), true);
            if (request()->wantsJson()) {
                return response(json_encode(array('body' => $data)), 200)->header('Content-Type', 'application/json');
            } else {
                return view('table.response', array(
                    'allTabs' => $data,
                    'tableId' => $tableId
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
        //$input_data = $request->all();
        $table_auth = $request->header('Auth-Key');
        $teams = team_table_mapping::getTableByAuth(array($table_auth));
        $response = json_decode(json_encode($teams), true);
        if (empty($response)) {
            return response()->json(array('error' => 'authorization_failure'), 401);
        }
        $incoming_data = $request->all();
        $table_incr_id = $response[0]['id'];
        $dataSource = $incoming_data['socket_data_source'];
        unset($incoming_data['socket_data_source']);
        unset($incoming_data['_token']);
        $table_name = $response[0]['table_id'];
        $table_structure = $response[0]['table_structure'];
        $teamData = team_table_mapping::makeNewEntryInTable($table_name, $incoming_data, $table_structure);
        if (isset($teamData['error'])) {
            return response()->json($teamData, 400);
        } else {
            team_table_mapping::makeNewEntryForSource($table_incr_id, $dataSource);
            return response()->json($teamData);
        }
    }

    public function loadSelectedTableStructure($tableName)
    {
        $tableNames = team_table_mapping::getUserTablesNameById($tableName);
        $tableNameArr = json_decode(json_encode($tableNames), true);
        return view('configureTable', array('tableData' => $tableNameArr));
    }
    public function configureSelectedTable(Request $request)
    {
        $tableData = $request->input('tableData');

        if (empty($tableData)) {
            $arr['msg'] = "Nothing to added, Please add atleast one column";
            return response()->json($arr);
        }

        $tableId = $request->input('tableId');
        $tableNames = team_table_mapping::getUserTablesNameById($tableId);
        $tableNameArr = json_decode(json_encode($tableNames), true);
        
        $id = $tableNameArr[0]['id'];
        //$tableStructure = json_decode($tableNameArr[0]['table_structure'], TRUE);
        $tableStructure = array();
        foreach ($tableData as $key => $value) {
            if (empty($value['name'])) {
                $arr['msg'] = "Name Can't be empty";
                return response()->json($arr);
            }

            if(empty($value['type']))
            {
                $arr['msg'] = "type Can't be empty";
                return response()->json($arr);
            }

            $tableStructure[$value['name']] = array('type' => $value['type'], 'unique' => 'false', 'value' => $value['value']);
        }

        $tableStructure = json_encode($tableStructure);

            $defaultValeArray = explode(',', $value['value']);
            $arr_tojson = json_encode($defaultValeArray);
            $tableStructure[] = array(
                'table_id' => $id,
                'column_name' => $value['name'],
                'column_type_id' => $value['type'],
                'default_value' =>$arr_tojson,
                'is_unique' => 0,
                'created_at' =>  Carbon::now()->toDateTimeString(),
                'updated_at' =>  Carbon::now()->toDateTimeString()
            );
        }
        //$tableStructure = json_encode($tableStructure);
        TableStructure::insert($tableStructure);
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

                Schema::table($logTableName, function (Blueprint $table) use ($tableData)
                {
                    foreach($tableData as $key => $value)
                    {
                        $table->string($value['name']);
                    }
                });

                $paramArr['id'] = $tableAutoIncId;
                $paramArr['table_structure'] = $tableStructure;
                $paramArr['socketApi'] = $request->input('socketApi');
                $tableNameArr = team_table_mapping::updateTableStructure($paramArr);
            }
            catch (\Illuminate\Database\QueryException $ex)
            {
                // dd($ex->getMessage());
                $arr['msg'] = "Error in updation";
                return response()->json($arr);
            }

            $arr['msg'] = "Table Updated Successfuly";
            return response()->json($arr);
        }
        else
        {
            $arr['msg'] = "Table Not Found";
            return response()->json($arr);
        }
    }

    public function updateEntry(Request $request) {

        $update_details = $request->all();
        if (!isset($update_details['table_id'])) {
            return response()->json(array('error' => 'Invalid table id'));
        }
        $tableNames = team_table_mapping::getUserTablesNameById($update_details['table_id']);
        $tableNameArr = json_decode(json_encode($tableNames), true);

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

    public function getSearchedData($tableId, $query) {
        $tableNames = team_table_mapping::getUserTablesNameById($tableId);
        $tableNameArr = json_decode(json_encode($tableNames), true);
        $userTableName = $tableNameArr[0]['table_name'];
        $tableID = $tableNameArr[0]['table_id'];
        $tableStructure = $tableNameArr[0]['table_structure'];
        $userTableStructure = json_decode(json_decode(json_encode($tableStructure), true), TRUE);

        if (empty($tableID)) {
            echo "no table found";
            exit();
        } else {
            $users = \DB::table($tableID)->selectRaw('*');
            $count = 0;
            foreach ($userTableStructure as $key => $value) {
                if ($count == 0) {
                    $users->where($key, 'LIKE', '%' . $query . '%');
                } else {
                    $users->orWhere($key, 'LIKE', '%' . $query . '%');
                }
                $count++;
            }
            $data = $users->get();
            $results = $array = json_decode(json_encode($data), True);

            return view('table.response', array(
                'allTabs' => $results,
                'tableId' => $tableID
            ));
        }
    }

    function aasort(&$array, $key) {
        $sorter = array();
        $ret = array();
        reset($array);
        foreach ($array as $ii => $va) {
            $sorter[$ii] = $va[$key];
        }
        asort($sorter);
        foreach ($sorter as $ii => $va) {
            $ret[$ii] = $array[$ii];
        }
        $array = array_values($ret);
        return $array;
    }

}
