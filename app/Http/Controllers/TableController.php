<?php

namespace App\Http\Controllers;

use App\Activity as Act;
use App\Entity\Activity;
use App\Http\Helpers;
use App\Repositories\TableDetailRepositoryInterface;
use App\Tables;
use App\TableStructure;
use App\Tabs;
use App\team_table_mapping;
use App\Teams;
use App\Viasocket;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class TableController extends Controller
{
    protected $activity;
    protected $tableDetail;

    public function __construct(TableDetailRepositoryInterface $tableDetail)
    {
        $this->tableDetail = $tableDetail;
    }

    public function createTable(Request $request)
    {
        $randomAuth = str_random(15);
        $data1 = $request->input('tableData');
        if(!empty($data1)){
            $data = Helpers::aasort($data1, "ordering"); // Array sort by abhishek jain

            $resp = TableStructure::validateStructure($data);

            if (!empty($resp['error'])) {
                return response()->json($resp);
            }
        }else{
            $data = array();
        }
        $userTableName = $request->input('tableName');

        if (empty($userTableName)) {
            $arr = array("msg" => "Table Name Can't be empty", "error" => true);
            return response()->json($arr);
        }
        $userTableName = preg_replace('/\s+/', '_', $userTableName);

        $teamId = $request->input('teamId');

        $socketApi = $request->input('socketApi');
        $newEntryApi = $request->input('newEntryApi');
        $tableName = strtolower("main_" . $userTableName . '_' . $teamId);
        $logTableName = strtolower("log_" . $userTableName . '_' . $teamId);

        if (!Schema::hasTable($tableName)) {
            Tables::createMainTable($tableName, $data);
            Tables::createLogTable($logTableName);

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
            if(!empty($resp['data'])){
                foreach ($resp['data'] as $key => $value) {
                    $value['table_id'] = $autoIncId;
                    $resp['data'][$key] = $value;
                }
                #insert table structure in table
                TableStructure::insertTableStructure($resp['data']);
            }

            return response()->json($arr);
        } else {
            $arr['msg'] = "Table already exists. Please use different table name";
            return response()->json($arr);
        }
    }

    public function getUserAllTables()
    {
        $teams = session()->get('team_array');
        $teamIdArr = array();
        $teamNameArr = array();

        $user =  Auth::user();
        $email = $user['email'];
        //print_r($email);
        $readOnlytableLst = team_table_mapping::getUserTablesNameByEmail($email);



        foreach ($teams as $teamId => $teamName) {
            $teamNameArr[] = $teamName;
            $teamIdArr[] = $teamId;
        }
        session()->put('teamNames', $teamNameArr);
        session()->put('teams', $teams);

        $tableLst = $this->getUserTablesByTeamId($teamIdArr);
        $teamTables = $table_incr_id_arr = array();
        foreach ($tableLst as $key => $value) {
            $teamTables[$value['team_id']][] = $value;
            $table_incr_id_arr[] = $value['id'];
        }
        $data = json_decode(json_encode(team_table_mapping::getTableSourcesByTableIncrId($table_incr_id_arr)), true);

        $source_arr = array();
        foreach ($data as $key => $value) {
            $source_arr[$value['table_incr_id']][] = $value['source'];
        }

        return view('showTable', array(
            'teamsArr' => $teams,
            'source_arr' => $source_arr,
            'teamTables' => $teamTables,
            'readOnlyTables'=> $readOnlytableLst
        ));
    }

    public function getAllTablesForSocket(Request $request)
    {
        $team_ids = $request->input('teamIds');
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

    function getUserTablesByTeamId($teamIdArr)
    {
        $tableLst = team_table_mapping::getUserTablesByTeam($teamIdArr);
        $tableLstJson = json_decode(json_encode($tableLst), true);
        return $tableLstJson;
    }

    public function loadSelectedTable($tableId, $tabName = 'All')
    {
        //First Validate if the user has access to Table
        $teams = session()->get('team_array');
        $teamIdArr = array();
        $teamNameArr = array();
        $isGuestAccess = false;
        
        foreach ($teams as $teamId => $teamName) {
            $teamNameArr[] = $teamName;
            $teamIdArr[] = $teamId;
        }

        $tableLst = team_table_mapping::getUserTablesByTeamAndTableId($teamIdArr,$tableId);
       // print_r($tableLst);
        if(count($tableLst) == 0){
            $user =  Auth::user();
            $email = $user['email'];
            $tableLst = team_table_mapping::getUserTablesByEmailAndTableId($email,$tableId);
            if(!empty($tableLst[0]->parent_table_id)){
                $isGuestAccess = true;
            }
        }

        if(count($tableLst) == 0){
            return redirect()->route('tables');
        }

        $results = $this->processTableData($tableId, $tabName);
        $results['isGuestAccess'] = $isGuestAccess;
        return view('home', $results);
    }

    public function loadContacts($tableIdMain, $tabName, $pageSize)
    {
        $tabDataJson = Tables::TabDataBySavedFilter($tableIdMain, $tabName, $pageSize);
        return json_decode(json_encode($tabDataJson), true);
    }

    public function processTableData($tableId, $tabName)
    {
        $tableNames = team_table_mapping::getUserTablesNameById($tableId);
        $userTableStructure = TableStructure::formatTableStructureData($tableNames['table_structure']);
        if (empty($tableNames['table_id'])) {
            return array();
        } else {
            $tableIdMain = $tableNames['table_id'];
            $tableAuth = $tableNames['auth'];
            $orderNeed = Helpers::orderData($tableNames);
            array_unshift($orderNeed, 'id');
            $data = Tabs::getTabsByTableId($tableIdMain);
            $tabs = json_decode(json_encode($data), true);
            if ($tabName == "All") {
                $tabArray = array();
            } else {
                $tabSql = Tabs::where([['tab_name', $tabName], ['table_id', $tableIdMain]])->first(['query'])->toArray();
                $tabArray = json_decode($tabSql['query'], true);
            }

            $tabPaginateData = $this->loadContacts($tableIdMain, $tabName, 100);
            $tabData = $tabPaginateData['data'];
            if (!empty($tabData))
                $tabData = Helpers::orderArray($tabData, $orderNeed);

            $teamId = $tableNames['team_id'];
            $parentTableId = $tableNames['parent_table_id'];
            if(!empty($parentTableId)){
                $table = $this->tableDetail->get($parentTableId);
                $teammates = Teams::getTeamMembers($table->team_id);
            }else{
                $teammates = Teams::getTeamMembers($teamId);
            }

            $teammatesOptions = array();
            foreach ($teammates as $tkey => $tvalue) {
                $teammatesOptions[] = $tvalue['name'];
            }
            $filters = Tables::getFiltrableData($tableIdMain, $userTableStructure, $teammates);

            $coltypes = TableStructure::getTableColumnTypesArray($tableIdMain);

            $allTabCount = Tables::getCountOfTabsData($tableIdMain, "All",$coltypes);
            $arrTabCount = Tables::getAllTabsCount($tableIdMain, $tabs);

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
                'activeTabFilter' => $tabArray,
                'tableAuth' => $tableAuth);
        }
    }

    public function processFilterData($req, $tableId, $coltype,$condition='and', $pageSize = 100)
    {
        $columnsonly = team_table_mapping::getUserTablesColumnNameById($tableId);
        $colArr = array(0=>'id');
        foreach ($columnsonly as $col){
            $colArr[] =$col['column_name'];
        }
        $tableNames = team_table_mapping::getUserTablesNameById($tableId);
        $tableAuth = $tableNames['auth'];
        $userTableStructure = TableStructure::formatTableStructureData($tableNames['table_structure']);

        if (empty($tableNames['table_id'])) {
            return array();
        }

        $jsonData = $this->getAppliedFiltersData($req, $tableNames['table_id'], $coltype,$condition,$colArr, $pageSize);
        $data = json_decode(json_encode($jsonData), true);
        $results = $data['data'];
        unset($data['data']);

        $teamId = $tableNames['team_id'];
        $teammates = Teams::getTeamMembers($teamId);

        if(!empty($tableNames['parent_table_id'])){
            $isGuestAccess = true;
        }else
            $isGuestAccess =false;

        return array(
            'allTabs' => $results,
            'tableId' => $tableId,
            'teammates' => $teammates,
            'pagination' => $data,
            'structure' => $userTableStructure,
            'tableAuth' => $tableAuth,
            'isGuestAccess'=>$isGuestAccess);
    }

    # function get search for selected filters

    public function applyFilters(Request $request)
    {
        $req = (array)($request->filter);
        $coltype = ($request->coltype);
        $tableId = $request->tableId;
        $condition = $request->condition;
        $responseArray = $this->processFilterData($req, $tableId, $coltype,$condition, 100);
        if (request()->wantsJson()) {
            return response(json_encode(array('body' => $responseArray)), 400)
                ->header('Content-Type', 'application/json');
        } else {
            return view('table.response', $responseArray);
        }
    }

    public function deleteTableRecords($tableId, Request $request){
        $ids =(array)$request->ids;
        $tableNames = team_table_mapping::getUserTablesNameById($tableId);
        $tableIdMain = $tableNames['table_id'];
        Tables::markRecordsAsDeleted($tableIdMain, $ids);
    }
    public static function getAppliedFiltersData($reqs, $tableId, $coltype,$condition,$colArr=array(),$pageSize = 100)
    {
        if(empty($colArr))
            $users = DB::table($tableId)->selectRaw('*');
        else
            $users = DB::table($tableId)->selectRaw("`".implode("`,`", $colArr)."`");
        $flag=0;
        foreach ($reqs as $k => $req)
        {
            foreach (array_keys($req) as $paramName) {
                $colomntype = $coltype[$k][$paramName];
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
        //echo $users->toSql();die;
        $data = $users->latest('id')->paginate($pageSize);
        return $data;
    }

    function getTableDetailsByAuth($table_auth)
    {
        return team_table_mapping::getTableByAuth($table_auth);
    }

    public function add(Request $request)
    {
        try {
            $add_entry_flag = False;
            $table_auth = $request->header('Auth-Key');
            $dataSource = $request->header('data-source');
            $response = $this->getTableDetailsByAuth($table_auth);

            if (empty($response)) {
                return response()->json(array('error' => 'authorization_failure'), 401);
            }

            $incoming_data = $request->all();
            $table_incr_id = $response['id'];

            if (isset($incoming_data['data_source'])) {
                $dataSource = $incoming_data['data_source'];
            }
            if (!isset($incoming_data['edit_url_callback'])) {
                $add_entry_flag = True;
            }

            unset($incoming_data['data_source']);
            unset($incoming_data['_token']);
            unset($incoming_data['edit_url_callback']);

            $table_name = $response['table_id'];
            $table_structure = TableStructure::formatTableStructureData($response['table_structure']);
            $teamData = team_table_mapping::makeNewEntryInTable($table_name, $incoming_data, $table_structure);

            if (isset($teamData['error'])) {
                return response()->json($teamData, 400);
            } else {
                $user = Auth::user();
                if ($user) {
                    $webhook_url = '';
                    if ($add_entry_flag && !empty($response['new_entry_api'])) {
                        $webhook_url = $response['new_entry_api'];
                    } elseif (!$add_entry_flag && !empty($response['socket_api'])) {
                        $webhook_url = $response['socket_api'];
                    }

                    if ($webhook_url != '') {
                        $incoming_data = json_decode(json_encode($teamData['data']), true);
                        $incoming_data['auth_name'] = $user->first_name . " " . $user->last_name;
                        $incoming_data['auth_email'] = $user->email;
                        $incoming_data['change_log'] = $teamData['details'];
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
                $this->insertActivityData($table_name,$teamData);
                $arr['teamData'] = $teamData;
                $arr['user'] = $user;
                return response()->json($arr);
            }
        } catch (Exception $ex) {
            $arr['msg'] = "Error occurred";
            $arr['exception'] = $ex->getMessage();
            return response()->json($arr,500);
        }
    }

    public function insertActivityData($table_name, $teamData)
    {
        if(empty($teamData['action']))
            return false;
        $data['description'] = $teamData['success'];
        $data['action'] = $teamData['action'];
        $data['content_type'] = 'Entry';
        $data['content_id'] = $teamData['data']->id;
        if($teamData['action'] =='Update')
            $data['updated_at'] = date('Y-m-d H:i:s');
        else {
            $data['created_at'] = date('Y-m-d H:i:s');
        }
        $loggedInUser = Auth::user();
        if($loggedInUser)
            $data['userId'] = $loggedInUser->email;
        else
            $data['userId'] = '';
        $data['details'] = $teamData['details'];
        $data['old_data'] = $teamData['old_data'];
        $data['ipAddress'] = \Request::getClientIp(true);
        $log_table = 'log' . substr($table_name, 4);
        $this->activity = new Activity($log_table);
        $activityData = Act::getActivityData($data);
        $this->activity->addActivity($activityData);
    }

    public function getSelectedTableStructure($tableName, Request $request)
    {
        $tableNames = team_table_mapping::getUserTablesNameById($tableName);
        $tableNameArr = json_decode(json_encode($tableNames), true);
        $tableStructure = TableStructure::withColumns($tableNameArr['id']);
        $colDetails = TableStructure::formatTableStructureData($tableNames['table_structure']);

        return response()->json(array(
            'tableData' => $tableNameArr,
            'structure' => $tableStructure,
            'colDetails' => $colDetails
        ));
    }

    public function getSearchedData($tableId, $query)
    {
        $array = $this->getTableSearchData($tableId, $query, 100);
        return view('table.response', $array);
    }

    public function getTableSearchData($tableId, $query, $pageSize = 100)
    {
        $tableNames = team_table_mapping::getUserTablesNameById($tableId);
        $tableID = $tableNames['table_id'];
        $tableStructure = $tableNames['table_structure'];
        $tableAuth = $tableNames['auth'];
        if(!empty($tableNames['parent_table_id'])){
            $isGuestAccess = true;
        }else
            $isGuestAccess =false;
        $userTableStructure = TableStructure::formatTableStructureData($tableStructure);
        if (empty($tableID)) {
            echo "no table found";
            exit();
        } else {
            $users = DB::table($tableID)->selectRaw('*');
            $count = 0;

            foreach ($userTableStructure as $key => $value) {
                if ($count == 0) {
                    $users->where($key, 'LIKE', '%' . $query . '%');
                } else {
                    $users->orWhere($key, 'LIKE', '%' . $query . '%');
                }
                $count++;
            }

            $data = $users->latest('id')->paginate($pageSize);
            $results = json_decode(json_encode($data), True);
            $allTabs = $results['data'];

            $orderNeed = Helpers::orderData($tableNames);
            array_unshift($orderNeed, 'id');
			
            if (!empty($allTabs))
                $allTabs = Helpers::orderArray($allTabs, $orderNeed);			

            unset($results['data']);
            $teamId = $tableNames['team_id'];
            $teammates = Teams::getTeamMembers($teamId);
            return array(
                'allTabs' => $allTabs,
                'tableId' => $tableId,
                'teammates' => $teammates,
                'tableAuth' => $tableAuth,
                'pagination' => $results,
                'structure' => $userTableStructure,
                'isGuestAccess'=>$isGuestAccess
            );
        }
    }

    public function getAllTables(Request $request)
    {
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

    public function searchTableData(Request $request)
    {
        $tableDetails = $this->getTableDetailsByAuth($request->header('Auth-Key'));
        $pageSize = empty($request->get('pageSize')) ? 100 : $request->get('pageSize');
        $query = empty($request->get('query')) ? "" : $request->get('query');
        return $this->getTableSearchData($tableDetails['id'], $query, $pageSize);
    }

    public function filterTableData(Request $request)
    {
        $req = $request->all();
        $pageSize = empty($request->get('pageSize')) ? 100 : $request->get('pageSize');
        $tableDetails = $this->getTableDetailsByAuth($request->header('Auth-Key'));
        return $this->processFilterData($req, $tableDetails['id'],$condition='and', $pageSize);
    }

    public function getContacts(Request $request)
    {
        $tableDetails = $this->getTableDetailsByAuth($request->header('Auth-Key'));
        $pageSize = empty($request->get('pageSize')) ? 100 : $request->get('pageSize');

        $tabName = empty($request->get('filter')) ? 'All' : $request->get('filter');
        return $this->loadContacts($tableDetails['table_id'], $tabName, $pageSize);
    }

    public function getFilters(Request $request)
    {
        $tableDetails = $this->getTableDetailsByAuth($request->header('Auth-Key'));
        $tabData = Tabs::getTabsByTableId($tableDetails['table_id']);
        $tabs = json_decode(json_encode($tabData), true);
        return Tables::getAllTabsCount($tableDetails['table_id'], $tabs);
    }

    public function sendEmailSMS(Request $request)
    {
        $filter = (array)($request->filter);
        $formData = (array)($request->formData);
        $type = ($request->type);
        $tableId = $request->tableId;
        $tableNames = team_table_mapping::getUserTablesNameById($tableId);
        if (empty($tableNames['table_id'])) {
            return array();
        }

        $jsonData = $this->getAppliedFiltersData($filter, $tableNames['table_id'], 1000);
        $data = json_decode(json_encode($jsonData), true);
        $results = $data['data'];
        if (empty($results)) {
            return response(json_encode(array('message' => 'No record found to send')), 200)->header('Content-Type', 'application/json');
        }
        if ($type == 'email') {
            $response = $this->sendMail($formData, $results, $tableId);
        }
        if ($type == 'sms') {
            $response = $this->sendSMS($formData, $results, $tableId);
        }
        return $response;
    }

    public function sendMail($formData, $data, $tableId)
    {
        $from_email = $formData['from_email'];
        $from_name = $formData['from_name'];
        $email_column = $formData['email_column'];
        $subject = $formData['subject'];
        $mailContent = $formData['mailContent'];
        preg_match_all("~\##(.*?)\##~", $mailContent, $replaceKey);
        $insertParamArr = array();
        $i = 0;
        $findArr = array();
        $mailKey = "testKey";
        foreach ($data as $key => $value) {
            if (!isset($value[$email_column])) {
                return response(json_encode(array('message' => 'Email column not found')), 403)->header('Content-Type', 'application/json');
            }
            if (!empty($value)) {
                $name = $value['name'];
                $valArr = array();
                foreach ($replaceKey[1] as $index => $strName) {
                    if (isset($value[$strName])) {
                        $valArr[$index] = $value[$strName];
                        $findArr[$index] = "##$strName##";
                    } else {
                    }

                }
                $actualMailContent = str_replace($findArr, $valArr, $mailContent);
                $insertParamArr[$i] = array('to_email' => $value[$email_column], 'from_email' => $from_email, 'from_name' => $from_name, 'subject' => $subject, 'content' => $actualMailContent, 'status' => 0, 'mailKey' => $mailKey, 'tableId' => $tableId);
            }
            $i++;
        }
        $response = \App\sendMailSMS::insertMailDetials($insertParamArr);
        if ($response) {
            return response(json_encode(array('message' => 'Email Sent')), 200)->header('Content-Type', 'application/json');
        } else {
            return response(json_encode(array('message' => 'Error in sending, Please contact to support')), 403)->header('Content-Type', 'application/json');
        }
    }

    public function sendSMS($formData, $data, $tableId)
    {
        $senderId = $formData['sender'];
        $route = $formData['route'];
        $mobile_column = $formData['mobile_columnn'];
        $message = $formData['message'];
        preg_match_all("~\##(.*?)\##~", $message, $replaceKey);
        $insertParamArr = array();
        $i = 0;
        $findArr = array();
        $authkey = '125463';
        foreach ($data as $key => $value) {
            if (!isset($value[$mobile_column])) {
                return response(json_encode(array('message' => 'SMS column not found')), 403)->header('Content-Type', 'application/json');
            }
            if (!empty($value)) {
                $valArr = array();
                foreach ($replaceKey[1] as $index => $strName) {
                    if (isset($value[$strName])) {
                        $valArr[$index] = $value[$strName];
                        $findArr[$index] = "##$strName##";
                    } else {
                    }

                }
                $actualMsg = str_replace($findArr, $valArr, $message);
                $insertParamArr[$i] = array('senderId' => $senderId, 'message' => $actualMsg, 'number' => $value[$mobile_column], 'authkey' => $authkey, 'route' => $route, 'status' => 0, 'tableId' => $tableId);
            }
            $i++;
        }
        $response = \App\sendMailSMS::insertMessageDetials($insertParamArr);
        if ($response) {
            return response(json_encode(array('message' => 'SMS Sent')), 200)->header('Content-Type', 'application/json');
        } else {
            return response(json_encode(array('message' => 'Error in sending, Please contact to support')), 403)->header('Content-Type', 'application/json');
        }
    }
}
