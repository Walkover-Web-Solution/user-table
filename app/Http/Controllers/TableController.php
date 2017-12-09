<?php

namespace App\Http\Controllers;

use App\Tabs;
use App\Tables;
use App\team_table_mapping;
use Illuminate\Http\Request;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\TableStructure;
use GuzzleHttp;
use App\Viasocket;

class TableController extends Controller {

    public function createTable(Request $request) {
        $randomAuth = str_random(15);
        $data1 = $request->input('tableData');
        $data = $this->aasort($data1, "order"); // Array sort by abhishek jain
        $resp = TableStructure::validateStructure($data);

        if (!empty($resp['error'])) {
            return response()->json($resp);
        }

        $userTableName = $request->input('tableName');
        if (empty($userTableName)) {
            $arr['msg'] = "Table Name Can't be empty";
            $arr['error'] = TRUE;
            return response()->json($arr);
        }
        $teamId = $request->input('teamId');

        $socketApi = $request->input('socketApi');
        $tableName = "main_" . $userTableName . '_' . $teamId;
        $logTableName = "log_" . $userTableName . '_' . $teamId;

        $tableData = '';
        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) use ($data) {
                $table->increments('id');
                $table->charset = 'utf8';
                $table->collation = 'utf8_unicode_ci';
                foreach ($data as $key => $value) {
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
                foreach ($data as $key => $value) {
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

    public function loadSelectedTable($tableName) {
        $tableNames = team_table_mapping::getUserTablesNameById($tableName);
        $tableNameArr = json_decode(json_encode($tableNames), true);
        $userTableName = $tableNameArr[0]['table_name'];
        $userTableStructure = TableStructure::formatTableStructureData($tableNameArr[0]['table_structure']);
        if (empty($tableNameArr[0]['table_id'])) {
            echo "no table found";
            exit();
        } else {
            $tableId = $tableNameArr[0]['table_id'];
            $allTabs = \DB::table($tableId)->select('*')->get();
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
            $teamId = $tableNameArr[0]['team_id'];
            $teammates = $this->getTeamMembers($teamId);
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
                'teammates' => $teammates)
            );
        }
    }

    public function showGraphForTable($tableName) {
        $tableNames = team_table_mapping::getUserTablesNameById($tableName);
        $tableNameArr = json_decode(json_encode($tableNames), true);
        $userTableName = $tableNameArr[0]['table_name'];
        $userTableStructure = $tableNameArr[0]['table_structure'];
        $date_columns = array();
        $other_columns = array();
        foreach ($userTableStructure as $key => $value) {
            if ($value['column_type']['column_name'] == 'date')
                $date_columns[] = $value['column_name'];
            else if ($value['is_unique'] == "false")
                $other_columns[] = $value['column_name'];;
        }
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
        $tableNames = team_table_mapping::getUserTablesNameById($tableId);
        $tableNameArr = json_decode(json_encode($tableNames), true);
        $userTableName = $tableNameArr[0]['table_name'];
        $userTableStructure = TableStructure::formatTableStructureData($tableNameArr[0]['table_structure']);
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
            $teamId = $tableNameArr[0]['team_id'];
            $teammates = $this->getTeamMembers($teamId);

            return view('home', array(
                'activeTab' => $tabName,
                'tabs' => $tabs,
                'allTabs' => $tabData,
                'allTabCount' => $allTabCount,
                'arrTabCount' => $arrTabCount,
                'tableId' => $tableId,
                'userTableName' => $userTableName,
                'filters' => $filters,
                'structure' => $userTableStructure,
                'teammates' => $teammates,
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

                $teamId = $tableNameArr[0]['team_id'];
                $teammates = $this->getTeamMembers($teamId);

                return view('table.response', array(
                    'allTabs' => $data,
                    'tableId' => $tableId,
                    'teammates' => $teammates
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
        $table_auth = $request->header('Auth-Key');
        $teams = team_table_mapping::getTableByAuth(array($table_auth));
        $response = json_decode(json_encode($teams), true);

        if (empty($response)) {
            return response()->json(array('error' => 'authorization_failure'), 401);
        }

        $incoming_data = $request->all();
        // print_r($incoming_data);
        $table_incr_id = $response[0]['id'];
        
        $dataSource = $incoming_data['socket_data_source'];

        unset($incoming_data['socket_data_source']);
        unset($incoming_data['_token']);

        $table_name = $response[0]['table_id'];
        $table_structure = TableStructure::formatTableStructureData($response[0]['table_structure']);
        $teamData = team_table_mapping::makeNewEntryInTable($table_name, $incoming_data, $table_structure);

        if (isset($teamData['error'])) {
            return response()->json($teamData, 400);
        } else {
            $user = \Auth::user();
            if ($user) {
                $incoming_data['auth_name'] = $user->first_name . " " . $user->last_name;
                $incoming_data['auth_email'] = $user->email;

                $data_string = json_encode($incoming_data);

                $ch = curl_init($teams[0]->socket_api);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($data_string))
                );
                curl_exec($ch);
            }
            team_table_mapping::makeNewEntryForSource($table_incr_id, $dataSource);
            return response()->json($teamData);
        }
    }

    public function loadSelectedTableStructure($tableName) {
        $tableNames = team_table_mapping::getUserTablesNameById($tableName);
        $tableNameArr = json_decode(json_encode($tableNames), true);
        $tableStructure = TableStructure::withColumns($tableNameArr[0]['id']);

        return view('configureTable', array(
            'tableData' => $tableNameArr,
            'structure' => $tableStructure));
    }

    public function configureSelectedTable(Request $request) {
        $tableData = $request->input('tableData');

        if (empty($tableData)) {
            $arr['msg'] = "Nothing to added, Please add atleast one column";
            return response()->json($arr);
        }

        $tableId = $request->input('tableId');
        $tableNames = team_table_mapping::getUserTablesNameById($tableId);
        $tableNameArr = json_decode(json_encode($tableNames), true);
        $tableAutoIncId = $tableNameArr[0]['id'];
        $resp = TableStructure::validateStructure($tableData, $tableAutoIncId);
        TableStructure::insertTableStructure($resp['data']);
        $tableName = $tableNameArr[0]['table_id'];
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
                $paramArr['socketApi'] = $request->input('socketApi');
                $tableNameArr = team_table_mapping::updateTableStructure($paramArr);
            } catch (\Illuminate\Database\QueryException $ex) {
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
        //$userTableName = $tableNameArr[0]['table_name'];
        $tableID = $tableNameArr[0]['table_id'];
        $tableStructure = $tableNameArr[0]['table_structure'];
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

            $data = $users->get();
            $results = $array = json_decode(json_encode($data), True);
            $teamId = $tableNameArr[0]['team_id'];
            $teammates = $this->getTeamMembers($teamId);
            return view('table.response', array(
                'allTabs' => $results,
                'tableId' => $tableID,
                'teammates' => $teammates
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

    public function getTeamMembers($teamId) {
        try {
            $authToken = session()->get('socket_token');
            $client = new GuzzleHttp\Client();
            $request = $client->get(env('SOCKET_API_URL') . '/teams/' . $teamId . '/memberships.json', ['headers' => ['Authorization' => $authToken]]);
            $response = $request->getBody()->getContents();
            $team_response_arr = json_decode($response, true);


            $member_array = array(0 => array('email' => '', 'name' => 'No One'));
            foreach ($team_response_arr['memberships'] as $member) {
                $email = $member['user']['email'];
                if (empty($member['user']['first_name']) && empty($member['user']['last_name'])) {
                    $name = $email;
                } else {
                    $name = $member['user']['first_name'] . " " . $member['user']['last_name'];
                }
                $member_array[] = array('email' => $email, 'name' => $name);
            }
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $member_array = array(0 => array('email' => '', 'name' => 'No One'));
        }
        return $member_array;
    }

    public function getAllTables(Request $request) {
        $authToken = $request->header('Authorization');
        $response = Viasocket::getUserTeam($authToken);
        $teamIdArr = Viasocket::getTeamIdArray($response);
        return $this->getUserTablesByTeamId($teamIdArr);
    }

}
