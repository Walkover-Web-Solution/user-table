<?php

namespace App\Http\Controllers;

use App\Tabs;
use App\Tables;
use App\Teams;
use App\team_table_mapping;
use Illuminate\Http\Request;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\TableStructure;
use App\ColumnType;
use GuzzleHttp;
use App\Viasocket;

class TableController extends Controller {

    public function createTable(Request $request) {
        $randomAuth = str_random(15);
        $data1 = $request->input('tableData');
        $data = $this->aasort($data1, "ordering"); // Array sort by abhishek jain

        $resp = TableStructure::validateStructure($data);

        if (!empty($resp['error'])) {
            return response()->json($resp);
        }

        $userTableName = $request->input('tableName');

        if(empty($userTableName))
        {
            $arr = array("msg" => "Table Name Can't be empty", "error" => true);
            return response()->json($arr);
        }

        $teamId = $request->input('teamId');

        $socketApi = $request->input('socketApi');
        $newEntryApi = $request->input('newEntryApi');
        $tableName = "main_" . $userTableName . '_' . $teamId;
        $logTableName = "log_" . $userTableName . '_' . $teamId;

        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) use ($data) {
                $table->increments('id');
                $table->charset = 'utf8';
                $table->collation = 'utf8_unicode_ci';
                foreach ($data as $value) {
                    $value['name'] = preg_replace('/\s+/', '_', $value['name']);
                    if ($value['unique'] == 'true') {
                        $table->string($value['name'])->unique($value['name']);
                    } else {
                        $table->string($value['name'])->nullable();
                    }
                }
            });
            Schema::create($logTableName, function (Blueprint $table) use ($data) {
                $table->increments('id');
                foreach ($data as $value) {
                    $value['name'] = preg_replace('/\s+/', '_', $value['name']);
                    $table->string($value['name'])->nullable();
                }
            });

            $arr['msg'] = "Table Successfully created";
            // Make entry of table in team table mapping & store table structure
            $paramArr['table_name'] = $userTableName;
            $paramArr['table_id'] = $tableName;
            $paramArr['team_id'] = $teamId;
            $paramArr['auth'] = $randomAuth;
            $paramArr['socket_api'] = $socketApi;
            $paramArr['new_entry_api'] = $newEntryApi;

            $response = team_table_mapping::makeNewTableEntry($paramArr);
            $autoIncId = $response->id;
            foreach ($resp['data'] as $key => $value) {
                $value['table_id'] = $autoIncId;
                $resp['data'][$key] = $value;
            }
            #insert table structure in table
            TableStructure::insertTableStructure($resp['data']);

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
        foreach ($table_data as $value) {
            $table_array[$value['team_id']][$count]['table_id'] = $value['table_id'];
            $table_array[$value['team_id']][$count]['table_name'] = $value['table_name'];
            $table_array[$value['team_id']][$count]['structure'] = $value['table_structure'];
            $table_array[$value['team_id']][$count]['auth'] = $value['auth'];
            $count++;
        }
        $response_arr = array();
        $cnt = 0;
        foreach ($table_array as $team_id => $table_data) {
            $response_arr[$cnt]['team_id'] = $team_id;
            $response_arr[$cnt]['tables'] = $table_data;
            $cnt++;
        }
        return response()->json($response_arr);
    }

    function getUserTablesByTeamId($teamIdArr) {
        $tableLst = team_table_mapping::getUserTablesByTeam($teamIdArr);
        $tableLstJson = json_decode(json_encode($tableLst), true);
        return $tableLstJson;
    }

    public function getGraphDataForTable(Request $request) {
        $tableName = $request->input('tableName');
        $dateColumn = $request->input('dateColumn');
        $secondColumn = $request->input('secondColumn');

        $tableNames = team_table_mapping::getUserTablesNameByName($tableName);
        $tableNameArr = json_decode(json_encode($tableNames), true);
        $userTableName = $tableNameArr[0]['table_id'];

        $sql = "SELECT $secondColumn LabelColumn,Count($secondColumn) as Total FROM $userTableName group by $secondColumn";
        $tableData = Tables::getSQLData($sql);
        print_r(json_encode($tableData));
    }

    public function loadSelectedTable($tableName)
    {
        $tableNames = team_table_mapping::getUserTablesNameById($tableName);

        $userTableName = $tableNames['table_name'];
        $userTableStructure = TableStructure::formatTableStructureData($tableNames['table_structure']);
        if (empty($tableNames['table_id'])) {
            echo "no table found";
            exit();
        } else {
            $tableAuth = $tableNames['auth'];
            $tableId = $tableNames['table_id'];
            $allTabs = \DB::table($tableId)->select('*')->get();
            $allTabsDataUnorder = json_decode(json_encode($allTabs), true);
            $newarr = $orderNeed =array();
            foreach($tableNames['table_structure'] as $k=>$v)
            {
                $newarr[$v['column_name']] = $v;
            }

            foreach($newarr as $k=>$v)
            {
                if($v['display'] == 1)
                    $orderNeed[] = $k;
            }

            array_unshift($orderNeed, 'id');

            if(!empty($allTabsDataUnorder))
                $allTabsData = $this->orderArray($allTabsDataUnorder, $orderNeed);
            else
                $allTabsData = $allTabsDataUnorder;
            $data = Tabs::getTabsByTableId($tableId);
            $tabs = json_decode(json_encode($data), true);

            $filters = Tables::getFiltrableData($tableId);

            if(!empty($tabs))
            {
                foreach($tabs as $val)
                {
                    $tab_name = $val['tab_name'];
                    $tabCountData = Tables::TabDataBySavedFilter($tableId, $tab_name);
                    $tabCount = count($tabCountData);

                    $arrTabCount[] = array($tab_name => $tabCount);
                }
            }
            else
            {
                $arrTabCount = array();
            }

            $allTabCount = count($allTabsData);
            $teamId = $tableNames['team_id'];
            $teammates = Teams::getTeamMembers($teamId);

            return view('home', array(
                'activeTab' => 'All',
                'tabs' => $tabs,
                'allTabs' => $allTabsData,
                'allTabCount' => $allTabCount,
                'arrTabCount' => $arrTabCount,
                'tableId' => $tableName,
                'userTableName' => $userTableName,
                'filters' => $filters,
                'structure' => $userTableStructure,
                'teammates' => $teammates,
                'tableAuth' => $tableAuth)
            );
        }
    }

    public function showGraphForTable($tableName) {
        $tableNames = team_table_mapping::getUserTablesNameById($tableName);
        $userTableName = $tableNames['table_name'];
        $userTableStructure = $tableNames['table_structure'];
        $date_columns = array();
        $other_columns = array();
        foreach ($userTableStructure as $value) {
            if ($value['column_type']['column_name'] == 'date')
                $date_columns[] = $value['column_name'];
            else if ($value['is_unique'] == "false")
                $other_columns[] = $value['column_name'];;
        }
        if (empty($tableNames['table_id'])) {
            echo "no table found";
            exit();
        } else {
            $tableId = $tableNames['table_id'];
            $allTabs = \DB::table($tableId)
                    ->select('*')
                    ->get();
            $allTabsData = json_decode(json_encode($allTabs), true);
            $data = Tabs::getTabsByTableId($tableId);
            $tabs = json_decode(json_encode($data), true);


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

            return view('graph', array(
                'activeTab' => 'All',
                'date_columns' => $date_columns,
                'other_columns' => $other_columns,
                'tabs' => $tabs,
                'allTabs' => $allTabsData,
                'allTabCount' => $allTabCount,
                'tableId' => $tableName,
                'userTableName' => $userTableName,
                'structure' => $userTableStructure));
        }
    }

    public function loadSelectedTableFilterData($tableId, $tabName) {
        $results = $this->processTableData($tableId, $tabName);
        return view('home', $results);
    }

    public function loadContacts($tableIdMain, $tabName) {
        $tabDataJson = Tables::TabDataBySavedFilter($tableIdMain, $tabName);
        return json_decode(json_encode($tabDataJson), true);
    }

    public function processTableData($tableId, $tabName) {
        $tableNames = team_table_mapping::getUserTablesNameById($tableId);
        $userTableStructure = TableStructure::formatTableStructureData($tableNames['table_structure']);
        if (empty($tableNames['table_id'])) {
            return array();
        } else {
            $tableIdMain = $tableNames['table_id'];
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

            $tabData = $this->loadContacts($tableIdMain, $tabName);
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
            $teamId = $tableNames['team_id'];
            $teammates = Teams::getTeamMembers($teamId);

            return array(
                'activeTab' => $tabName,
                'tabs' => $tabs,
                'allTabs' => $tabData,
                'allTabCount' => $allTabCount,
                'arrTabCount' => $arrTabCount,
                'tableId' => $tableId,
                'userTableName' => $tableNames['table_name'],
                'filters' => $filters,
                'structure' => $userTableStructure,
                'teammates' => $teammates,
                'activeTabFilter' => $tabArray);
        }
    }

    public function processFilterData($req, $tableId, $pageSize = 20) {
        $tableNames = team_table_mapping::getUserTablesNameById($tableId);
        if (empty($tableNames['table_id'])) {
            return array();
        }

        $jsonData = $this->getAppliedFiltersData($req, $tableNames['table_id'], $pageSize);
        $data = json_decode(json_encode($jsonData), true);
        $results = $data['data'];
        unset($data['data']);

        $teamId = $tableNames['team_id'];
        $teammates = Teams::getTeamMembers($teamId);

        return array(
            'allTabs' => $results,
            'tableId' => $tableId,
            'teammates' => $teammates,
            'pagination' => $data
        );
    }

    # function get search for selected filters

    public function applyFilters(Request $request) {
        $req = (array) ($request->filter);

        $tableId = $request->tableId;
        $responseArray = $this->processFilterData($req, $tableId, 30);
        if (request()->wantsJson()) {
            return response(json_encode(array('body' => $responseArray)), 400)
                            ->header('Content-Type', 'application/json');
        } else {
            return view('table.response', $responseArray);
        }
    }

    public static function getAppliedFiltersData($req, $tableId, $pageSize = 20) {
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
        $data = $users->paginate($pageSize);

        return $data;
    }

    function getTableDetailsByAuth($table_auth) {
        return team_table_mapping::getTableByAuth($table_auth);
    }

    public function add(Request $request) {
        $add_entry_flag = False;
        $table_auth = $request->header('Auth-Key');
        $response = $this->getTableDetailsByAuth($table_auth);

        if (empty($response)) {
            return response()->json(array('error' => 'authorization_failure'), 401);
        }

        $incoming_data = $request->all();
        $table_incr_id = $response['id'];
        
        $dataSource = $incoming_data['socket_data_source'];
        if(!isset($incoming_data['edit_url_callback'])){
            $add_entry_flag = True;
        }

        unset($incoming_data['socket_data_source']);
        unset($incoming_data['_token']);
        unset($incoming_data['edit_url_callback']);

        $table_name = $response['table_id'];
        $table_structure = TableStructure::formatTableStructureData($response['table_structure']);
        $teamData = team_table_mapping::makeNewEntryInTable($table_name, $incoming_data, $table_structure);

        if (isset($teamData['error'])) {
            return response()->json($teamData, 400);
        } else {
            $user = \Auth::user();
            if ($user) {
                $webhook_url = '';
                if($add_entry_flag && !empty($response['new_entry_api'])){
                    $webhook_url = $response['new_entry_api'];
                }
                elseif(!$add_entry_flag && !empty($response['socket_api']) ){
                    $webhook_url = $response['socket_api'];
                }
                
                if ($webhook_url != '') {

                    $incoming_data['auth_name'] = $user->first_name . " " . $user->last_name;
                    $incoming_data['auth_email'] = $user->email;

                    $data_string = json_encode($incoming_data);

                    $ch = curl_init($webhook_url);
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        'Content-Type: application/json',
                        'Content-Length: ' . strlen($data_string))
                    );
                    curl_exec($ch);
                }
            }
            team_table_mapping::makeNewEntryForSource($table_incr_id, $dataSource);
            return response()->json($teamData);
        }
    }

    public function loadSelectedTableStructure($tableName) {
        $tableNames = team_table_mapping::getUserTablesNameById($tableName);
        $tableStructure = TableStructure::withColumns($tableNames['id']); // This data already come in above table

        $ColumnType = ColumnType::all();
        $new_arr = array();
        foreach($tableNames['table_structure'] as $k=>$v)
        {
            $new_arr[$v['column_name']] = $v;
        }

        return view('configureTable', array(
            'tableData' => $tableNames,
            'structure' => $tableStructure,
            'sequence' => $new_arr,
            'columnList' => $ColumnType));
    }

    public function getSelectedTableStructure($tableName, Request $request) {
        $tableNames = team_table_mapping::getUserTablesNameById($tableName);
        $tableNameArr = json_decode(json_encode($tableNames), true);
        $tableStructure = TableStructure::withColumns($tableNameArr['id']);

        return response()->json(array(
                    'tableData' => $tableNameArr,
                    'structure' => $tableStructure));
    }

    public function configureSelectedTable(Request $request)
    {
        $tableData = $request->input('tableData');
        $tableOldData = $request->input('tableOldData');
        if(!empty($tableData))
            $newTableStructure = array_merge($tableData, $tableOldData);
        else
            $newTableStructure = $tableOldData;

        $newTableStructure = $this->aasort($newTableStructure, "ordering");


        $tableId = $request->input('tableId');
        $tableNames = team_table_mapping::getUserTablesNameById($tableId);

        $tableAutoIncId = $tableNames['id'];
        $resp = TableStructure::validateStructure($newTableStructure, $tableAutoIncId);
        if(isset($resp['error'])){
            return response()->json($resp);
        }

        //TableStructure::deleteTableStructure($tableNames['id']);
        //TableStructure::insertTableStructure($resp['data']);
        TableStructure::updateStructureInBulk($resp['data']);

        $tableName = $tableNames['table_id'];
        $logTableName = "log_" . $tableNames['table_name'] . "_" . $tableNames['team_id'];

        if (Schema::hasTable($tableName)) {
            if(!empty($tableData))
            {
                try {
                    Schema::table($tableName, function (Blueprint $table) use ($tableData) {
                        foreach ($tableData as $value) {
                            $table->string($value['name']);
                        }
                    });

                    Schema::table($logTableName, function (Blueprint $table) use ($tableData) {
                        foreach ($tableData as $value) {
                            $table->string($value['name']);
                        }
                    });

                    $paramArr['id'] = $tableAutoIncId;
                    $paramArr['socketApi'] = $request->input('socketApi');
                    $paramArr['new_entry_api'] = $request->input('newEntryApi');
                    team_table_mapping::updateTableStructure($paramArr);
                } catch (\Illuminate\Database\QueryException $ex) {
                    $arr['msg'] = "Error in updation";
                    return response()->json($arr);
                }
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

        $tableName = $tableNames['table_id'];
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
        $array = $this->getTableSearchData($tableId, $query, 30);
        return view('table.response', $array);
    }

    public function getTableSearchData($tableId, $query, $pageSize = 20) {
        $tableNames = team_table_mapping::getUserTablesNameById($tableId);
        $tableID = $tableNames['table_id'];
        $tableStructure = $tableNames['table_structure'];
        $userTableStructure = TableStructure::formatTableStructureData($tableStructure);
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

            $data = $users->paginate($pageSize);
            $results = json_decode(json_encode($data), True);
            $allTabs = $results['data'];
            unset($results['data']);
            $teamId = $tableNames['team_id'];
            $teammates = Teams::getTeamMembers($teamId);
            return array(
                'allTabs' => $allTabs,
                'tableId' => $tableId,
                'teammates' => $teammates,
                'pagination' => $results
            );
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

    function orderArray($arrayToOrder, $keys)
    {
        foreach($arrayToOrder as $val)
        {
            foreach ($keys as $key)
            {
                $inner_ordered[$key] = $val[$key];
            }
            $ordered[] = $inner_ordered;
        }

        return $ordered;
    }

    public function getAllTables(Request $request) {
        $authToken = $request->header('Authorization');
        $response = Viasocket::getUserTeam($authToken);
        $teamIdArr = Viasocket::getTeamIdArray($response);
        return $this->getUserTablesByTeamId($teamIdArr);
    }

    /*
      @param table auth key from header
      @param search string in query param
      api function to search table details
     */

    public function searchTableData(Request $request) {
        $tableDetails = $this->getTableDetailsByAuth($request->header('Auth-Key'));
        $pageSize = empty($request->get('pageSize')) ? 100 : $request->get('pageSize');
        $query = empty($request->get('query')) ? "" : $request->get('query');
        return $this->getTableSearchData($tableDetails['id'], $query, $pageSize);
    }

    public function filterTableData(Request $request) {
        $req = $request->all();
        $pageSize = empty($request->get('pageSize')) ? 100 : $request->get('pageSize');
        $tableDetails = $this->getTableDetailsByAuth($request->header('Auth-Key'));
        return $this->processFilterData($req, $tableDetails['id'], $pageSize);
    }

    public function getContacts(Request $request) {
        $tableDetails = $this->getTableDetailsByAuth($request->header('Auth-Key'));
        $pageSize = empty($request->get('pageSize')) ? 100 : $request->get('pageSize');
        $tabName = empty($request->get('filter')) ? 'All' : $request->get('filter');
        return $this->loadContacts($tableDetails['table_id'], $tabName);
    }

}
