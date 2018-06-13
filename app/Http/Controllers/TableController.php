<?php

namespace App\Http\Controllers;

use App\Activity as Act;
use App\Entity\Activity;
use App\Http\Helpers;
use App\Repositories\TableDetailRepositoryInterface;
use App\Tables;
use App\TableStructure;
use App\Tabs;
use App\CronTab;
use App\team_table_mapping;
use App\Teams;
use App\Viasocket;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use App\Repositories\TableRepository;
use App\SMS;
use App\ColumnType;
use Illuminate\Support\Facades\Input;

class TableController extends Controller {

    protected $activity;
    protected $tableDetail;
    public $tableRepository;

    public function __construct(TableDetailRepositoryInterface $tableDetail, TableRepository $tableRepository) {
        $this->tableDetail = $tableDetail;
        $this->tableRepository = $tableRepository;
    }

    public function createTable(Request $request) {
        $reservedKeywords = json_decode(file_get_contents('reserved_keywords.json'));

        $tableName = strtolower($request->tableName);

        if(in_array($tableName , $reservedKeywords))
        {
            $arr['msg'] = "Reserved Keyword. Please use different table name";
            $arr["error"] = true;
            return response()->json($arr);
        }

        $randomAuth = str_random(15);
        $data1 = $request->input('tableData');
        if (!empty($data1)) {
            $data = Helpers::aasort($data1, "ordering"); // Array sort by abhishek jain

            $resp = TableStructure::validateStructure($data);

            if (!empty($resp['error'])) {
                return response()->json($resp);
            }
        } else {
            $data = array();
        }
        $userTableName = $request->input('tableName');

        if (empty($userTableName)) {
            $arr = array("msg" => "Table Name Can't be empty", "error" => true);
            return response()->json($arr);
        }
        
        $userTableName = preg_replace('/\s+/', '_', $userTableName);

        $userTableName = str_replace('-' , '_' , $userTableName);

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
            if (!empty($resp['data'])) {
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
        $response = $this->tableRepository->getUserAllTables();
        return view('showTable', $response);
    }

    public function getAllTablesForSocket(Request $request) {
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

    function getUserTablesByTeamId($teamIdArr) {
        $tableLst = team_table_mapping::getUserTablesByTeam($teamIdArr);
        $tableLstJson = json_decode(json_encode($tableLst), true);
        return $tableLstJson;
    }

    public function loadSelectedTable($tableId, $tabName = 'All') {
        //First Validate if the user has access to Table
        $teams = session()->get('team_array');
        $teamIdArr = array();
        $teamNameArr = array();
        $isGuestAccess = false;

        foreach ($teams as $teamId => $teamName) {
            $teamNameArr[] = $teamName;
            $teamIdArr[] = $teamId;
        }

        $tableLst = team_table_mapping::getUserTablesByTeamAndTableId($teamIdArr, $tableId);

        if (count($tableLst) == 0) {
            $user = Auth::user();
            $email = $user['email'];
            $tableLst = team_table_mapping::getUserTablesByEmailAndTableId($email, $tableId);
            if (!empty($tableLst[0]->parent_table_id)) {
                $isGuestAccess = true;
            }
        }

        if (count($tableLst) == 0) {
            return redirect()->route('tables');
        }

        $sortArray = array();

        if(Input::get('sort-key')!=null && Input::get('sort-key')!="")
            $sortArray['sort-key'] = Input::get('sort-key');

        if(Input::get('sort-order')!=null && Input::get('sort-order')!="")
            $sortArray['sort-order'] = Input::get('sort-order');

        $results = $this->processTableData($tableId, $tabName , $sortArray);
        $results['isGuestAccess'] = $isGuestAccess;

        $columnTypes = ColumnType::all();

        $results['columnTypes'] = $columnTypes;
        
        $results['sortArray'] = $sortArray;

        return view('home', $results);
    }

    public function loadContacts($tableIdMain, $tabName, $pageSize, $condition , $sortArray = array()) {
        $tabDataJson = Tables::TabDataBySavedFilter($tableIdMain, $tabName, $pageSize, $condition , $sortArray);
        return json_decode(json_encode($tabDataJson), true);
    }

    public function processTableData($tableId, $tabName , $sortArray) {
        $tableNames = team_table_mapping::getUserTablesNameById($tableId);
        if (empty($tableNames['table_id'])) {
            return array();
        } else {
            $tableIdMain = $tableNames['table_id'];
            $tableAuth = $tableNames['auth'];
            $tableSmsApi = !empty($tableNames['sms_api_key']) ? $tableNames['sms_api_key'] : false;
            $tableEmailApi = !empty($tableNames['email_api_key']) ? $tableNames['email_api_key'] : false;
            $parentTableId = $tableNames['parent_table_id'];
            $teamId = $tableNames['team_id'];
            $date_columns = array();
            $other_columns = array();
            if (!empty($parentTableId)) {
                $table = $this->tableDetail->get($parentTableId);
                $subtable = $this->tableDetail->get($tableId);
                $arrayAllowed = json_decode($subtable->table_structure, true);
                $teammates = Teams::getTeamMembers($table->team_id);
                $tableNames = team_table_mapping::getUserTablesNameById($parentTableId, $arrayAllowed);
            } else {
                $teammates = Teams::getTeamMembers($teamId);
            }
            $graphtableNames = team_table_mapping::getUserTablesNameById($tableId);
            $userTableStructure = $graphtableNames['table_structure'];
            foreach ($userTableStructure as $key => $value) {
                if ($value['column_type']['column_name'] == 'date')
                    $date_columns[] = $value['column_name'];
                else if ($value['is_unique'] == "false") {
                    $col = $value['column_name'];
                    $sql = "SELECT count(distinct `$col`) allrecords,count(`$col`) total  FROM $tableIdMain";
                    $tableData = Tables::getSQLData($sql);
                    $allrecords = $tableData[0]->allrecords;
                    $total = $tableData[0]->total;
                    if (($allrecords != 1 && $allrecords != $total && ($total - $allrecords) > 5))
                        $other_columns[] = $col;
                }
            }

            $userTableStructure = TableStructure::formatTableStructureData($tableNames['table_structure']);

            $orderNeed = Helpers::orderData($tableNames);
            array_unshift($orderNeed, 'id');
            //$data = Tabs::getTabsByTableId($tableIdMain);
            //$tabs = json_decode(json_encode($data), true);
            $filtercolumns = array();
            if ($tabName == "All") {
                $tabArray = array();
                $tabcondition = 'and';
                $filtercolumns = array();
            } else {
                $tabSql = Tabs::where([['tab_name', $tabName], ['table_id', $tableIdMain]])->first(['query', 'condition', 'id'])->toArray();
                $tabArray = json_decode($tabSql['query'], true);
                $tabcondition = isset($tabSql['condition']) && !empty($tabSql['condition']) ? $tabSql['condition'] : 'and';
                if (isset($tabSql['id']) && !empty($tabSql['id'])) {
                    $tab_id = $tabSql['id'];
                    $filter_columns = TableStructure::whereIn('id', function($query) use ($tab_id) {
                                $query->select('column_id')->from('tab_column_mappings')
                                        ->Where('tab_id', '=', $tab_id);
                            })->get();
                    foreach ($filter_columns as $value)
                        $filtercolumns[] = $value->column_name;
                }
            }
            
            $tabPaginateData = $this->loadContacts($tableIdMain, $tabName, 100, $tabcondition , $sortArray);
            $tabData = $tabPaginateData['data'];
            if (!empty($tabData))
                $tabData = Helpers::orderArray($tabData, $orderNeed);

            $teammatesOptions = array();
            foreach ($teammates as $tvalue) {
                $teammatesOptions[] = $tvalue['name'];
            }
            $filters = Tables::getFiltrableData($tableIdMain, $userTableStructure, $teammates);

            $coltypes = TableStructure::getTableColumnTypesArray($tableIdMain);

            $allTabCount = Tables::getCountOfTabsData($tableIdMain, "All", $coltypes);
            //$arrTabCount = Tables::getAllTabsCount($tableIdMain, $tabs);
            $d = strtotime("-3 days");
            $rangeStart = date('m/d/Y', $d);
            $d1 = strtotime("+3 days");
            $rangeEnd = date('m/d/Y', $d1);
            $allCount = isset($tabPaginateData['total'])?$tabPaginateData['total']:$allTabCount;
            return array(
                'allCount' =>$allCount,
                'activeTab' => $tabName,
                'date_columns' => $date_columns,
                'other_columns' => $other_columns,
                'allTabs' => $tabData,
                'allTabCount' => $allTabCount,
                'tableId' => $tableId,
                'userTableName' => $tableNames['table_name'],
                'filters' => $filters,
                'structure' => $userTableStructure,
                'teammates' => $teammates,
                'activeTabFilter' => $tabArray,
                'tabcondition' => $tabcondition,
                'tableAuth' => $tableAuth,
                'tableSmsApi' => $tableSmsApi,
                'tableEmailApi' => $tableEmailApi,
                'rangeStart' => $rangeStart,
                'rangeEnd' => $rangeEnd,
                'filtercolumns' => $filtercolumns);
        }
    }
    public function getTableFilters($tableId, $activeTab) {
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
            $arrTabCount = Tables::getAllTabsCount($tableIdMain, $tabs);
            $htmlData = "";
            foreach ($arrTabCount as $tabDetail) {
                foreach ($tabDetail as $tabName => $tabCount) {
                    if ($activeTab == $tabName)
                        $htmlData .= '<li role="presentation" class="active">';
                    else
                        $htmlData .= '<li role="presentation">';
                    $htmlData .= '<a href="' . env('APP_URL') . '/tables/' . $tableId . '/filter/' . $tabName . '">' . $tabName . ' (' . $tabCount . ')</a></li>';
                }
            }
            return response()->json($htmlData);
        }
    }
    public function processFilterData($req, $tableId, $coltype, $condition = 'and', $pageSize = 100) {
        $columnsonly = team_table_mapping::getUserTablesColumnNameById($tableId);
        usort($columnsonly, function ($a, $b) {
            return strnatcmp($a['ordering'], $b['ordering']);
        });
        $colArr = array(0 => 'id');
        foreach ($columnsonly as $col) {
            $colArr[] = $col['column_name'];
        }
        $tableNames = team_table_mapping::getUserTablesNameById($tableId);
        $tableAuth = $tableNames['auth'];
        $tableSmsApi = !empty($tableNames['sms_api_key']) ? $tableNames['sms_api_key'] : false;
        $tableEmailApi = !empty($tableNames['email_api_key']) ? $tableNames['email_api_key'] : false;
        $userTableStructure = TableStructure::formatTableStructureData($tableNames['table_structure']);

        if (empty($tableNames['table_id'])) {
            return array();
        }

        $jsonData = $this->getAppliedFiltersData($req, $tableNames['table_id'], $coltype, $condition, $colArr, $pageSize);
        $data = json_decode(json_encode($jsonData), true);
        $results = $data['data'];
        unset($data['data']);

        $teamId = $tableNames['team_id'];
        $teammates = Teams::getTeamMembers($teamId);

        if (!empty($tableNames['parent_table_id'])) {
            $isGuestAccess = true;
        } else
            $isGuestAccess = false;
        
        $allTabCount = Tables::getCountOfTabsData($tableNames['table_id'], "All", $coltype);
        return array(
            'allTabCount' => $allTabCount,
            'allCount' =>$data['total'],
            'allTabs' => $results,
            'tableId' => $tableId,
            'teammates' => $teammates,
            'pagination' => $data,
            'structure' => $userTableStructure,
            'tableAuth' => $tableAuth,
            'tableSmsApi' => $tableSmsApi,
            'tableEmailApi' => $tableEmailApi,
            'isGuestAccess' => $isGuestAccess);
    }

    # function get search for selected filters

    public function applyFilters(Request $request) {
        $req = (array) ($request->filter);
        $coltype = ($request->coltype);
        $tableId = $request->tableId;
        $condition = $request->condition;
        $responseArray = $this->processFilterData($req, $tableId, $coltype, $condition, 100);
        if (request()->wantsJson()) {
            return response(json_encode(array('body' => $responseArray)), 400)
                            ->header('Content-Type', 'application/json');
        } else {
            return view('table.response', $responseArray);
        }
    }

    public function deleteTableRecords($tableId, Request $request) {
        $ids = (array) $request->ids;
        $tableNames = team_table_mapping::getUserTablesNameById($tableId);
        $tableIdMain = $tableNames['table_id'];
        Tables::markRecordsAsDeleted($tableIdMain, $ids);
    }

    public static function getAppliedFiltersData($reqs, $tableId, $coltype, $condition, $colArr = array(), $pageSize = 100) {
        if (empty($colArr))
            $users = DB::table($tableId)->selectRaw('*');
        else
            $users = DB::table($tableId)->selectRaw("`" . implode("`,`", $colArr) . "`");

        $users = Tables::getConditionQuery($reqs, $coltype, $condition, $users, $tableId);
        //echo $users->toSql();die;
        if ($pageSize == null)
            $pageSize = $users->count();

        $data = $users->latest('id')->paginate($pageSize);
        return $data;
    }

    function getTableDetailsByAuth($table_auth) {
        return team_table_mapping::getTableByAuth($table_auth);
    }

    public function add(Request $request) {
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

            $this->_performActions($response['table_id'] , $incoming_data , $teamData['data']->id);

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
                if($teamData['success']=="Entry Updated")
                {
                    $tableStructure = \DB::table('table_structures')->select('column_name' , 'column_type_id')->where('table_id' , $table_incr_id)->get();
                    $columnDetails = array();
                    foreach($tableStructure as $tabStr)
                    {
                        $columnDetails[$tabStr->column_name] = $tabStr->column_type_id;
                    }
                    foreach($teamData['details'] as $key => $value)
                    {
                        if(isset($columnDetails[$key]) && $columnDetails[$key]==9)
                        {
                            $teamData['details'][$key] = date('Y-m-d' , $value);
                        }
                    }
                    foreach($teamData['old_data'] as $key => $value)
                    {
                        if(isset($columnDetails[$key]) && $columnDetails[$key]==9)
                        {
                            $teamData['old_data'][$key] = date('Y-m-d' , $value);
                        }
                    }
                }
                // return response()->json($teamData);
                $this->insertActivityData($table_name, $teamData);
                $arr['teamData'] = $teamData;
                $arr['user'] = $user;
                return response()->json($arr);
            }
        } catch (Exception $ex) {
            $arr['msg'] = "Error occurred";
            $arr['exception'] = $ex->getMessage();
            return response()->json($arr, 500);
        }
    }

    public function insertActivityData($table_name, $teamData) {
        if (empty($teamData['action']))
            return false;
        $data['description'] = $teamData['success'];
        $data['action'] = $teamData['action'];
        $data['content_type'] = 'Entry';
        $data['content_id'] = $teamData['data']->id;
        if ($teamData['action'] == 'Update')
            $data['updated_at'] = date('Y-m-d H:i:s');
        else {
            $data['created_at'] = date('Y-m-d H:i:s');
        }
        $loggedInUser = Auth::user();
        if ($loggedInUser)
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

    public function getSelectedTableStructure($tableName, Request $request) {
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

    public function getSearchedData($tableId, $query) {
        $array = $this->getTableSearchData($tableId, $query, 100);
        return view('table.response', $array);
    }

    public function getTableSearchData($tableId, $query, $pageSize = 100) {
        $tableNames = team_table_mapping::getUserTablesNameById($tableId);
        $tableID = $tableNames['table_id'];
        $tableStructure = $tableNames['table_structure'];
        $tableAuth = $tableNames['auth'];
        $tableSmsApi = !empty($tableNames['sms_api_key']) ? $tableNames['sms_api_key'] : false;
        $tableEmailApi = !empty($tableNames['email_api_key']) ? $tableNames['email_api_key'] : false;
        if (!empty($tableNames['parent_table_id'])) {
            $isGuestAccess = true;
        } else
            $isGuestAccess = false;
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
                'allCount'=> $results['total'],
                'allTabs' => $allTabs,
                'tableId' => $tableId,
                'teammates' => $teammates,
                'tableAuth' => $tableAuth,
                'tableSmsApi' => $tableSmsApi,
                'tableEmailApi' => $tableEmailApi,
                'pagination' => $results,
                'structure' => $userTableStructure,
                'isGuestAccess' => $isGuestAccess
            );
        }
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
        return $this->processFilterData($req, $tableDetails['id'], $condition = 'and', $pageSize);
    }

    public function getContacts(Request $request) {
        $tableDetails = $this->getTableDetailsByAuth($request->header('Auth-Key'));
        $pageSize = empty($request->get('pageSize')) ? 100 : $request->get('pageSize');

        $tabName = empty($request->get('filter')) ? 'All' : $request->get('filter');
        return $this->loadContacts($tableDetails['table_id'], $tabName, $pageSize);
    }

    public function getFilters(Request $request) {
        $tableDetails = $this->getTableDetailsByAuth($request->header('Auth-Key'));
        $tabData = Tabs::getTabsByTableId($tableDetails['table_id']);
        $tabs = json_decode(json_encode($tabData), true);
        return Tables::getAllTabsCount($tableDetails['table_id'], $tabs);
    }

    public function sendEmailSMS(Request $request) {
        $filter = (array) ($request->filter);
        $formData = (array) ($request->formData);
        $coltype = (array) ($request->coltype);
        $condition = $request->condition;
        $type = ($request->type);
        $tableId = $request->tableId;
        $activeTab = $request->activeTab;
        $tabId = false;
        $tableNames = team_table_mapping::getUserTablesNameById($tableId);
        if (empty($tableNames['table_id'])) {
            return array();
        }

        if($activeTab != 'All')
        {
            $tabresult = Tabs::getTabQuerywithtable($activeTab, $tableNames['table_id']);
            if(!empty($tabresult))
                $tabId = $tabresult;
        }else{
            return response(json_encode(array('status' => 'error', 'message' => 'Please save your segment first.')), 200)->header('Content-Type', 'application/json');
        }
        
        $columnsonly = team_table_mapping::getUserTablesColumnNameById($tableId);
        usort($columnsonly, function ($a, $b) {
            return strnatcmp($a['ordering'], $b['ordering']);
        });
        $colArr = array(0 => 'id');
        foreach ($columnsonly as $col) {
            $colArr[] = $col['column_name'];
        }

        if (!empty($filter))
            $jsonData = $this->getAppliedFiltersData($filter, $tableNames['table_id'], $coltype, $condition, $colArr, null);
        else
            $jsonData = Tables::TabDataBySavedFilter($tableNames['table_id'], 'All', null, $condition);

        $data = json_decode(json_encode($jsonData), true);
        $results = $data['data'];
        if (empty($results)) {
            return response(json_encode(array('message' => 'No record found to send')), 200)->header('Content-Type', 'application/json');
        }
        if ($type == 'email') {
            $response = SMS::sendMail($formData, $results, $tableId, $tableNames['email_api_key'], $tabId);
        }
        if ($type == 'sms') {
            $response = SMS::sendSMS($formData, $results, $tableId, $tableNames['sms_api_key'], $tabId);
        }
        return $response;
    }
    
    public function importTable(Request $request) {
        $file = $request->file('importTable');

        $fileName = time() . '_' . rand(111111, 999999) . '.' . $file->getClientOriginalExtension();

        if ($file->getClientOriginalExtension() == "csv") {
            //Move Uploaded File
            $destinationPath = 'uploads';
            $file->move($destinationPath, $fileName);

            $handle = fopen($destinationPath . '/' . $fileName, "r");

            $i = 0;
            $csvDataArray = array();
            while ($csvLine = fgetcsv($handle)) {
                $csvDataArray[$i] = $csvLine;
                $i++;
            }
            $formattedArray = $csvDataArray;
            return response()->json(['Message' => 'Success', 'Status' => '200', 'Data' => $formattedArray, 'FileName' => $fileName])->setStatusCode(200);
        }
    }

    public function mapDataToTable(Request $request) {
        $formData = $request->toArray();
        $validator = \Validator::make($formData, [
            'mappingValue' => 'required',
            'fileName' => 'required'
        ]);
        if ($validator->fails())
        {
            return response()->json(['Message' => 'Failed', 'Status' => '422', 'Data' => $validator->errors()])->setStatusCode(422);
        }

        $destinationPath = 'uploads';

        $handle = fopen($destinationPath . '/' . $request->fileName, "r");

        $response = $this->getTableDetailsByAuth($request->tableAuthKey);

        $table_name = $response['table_id'];
        $table_incr_id = $response['id'];
        $table_structure = TableStructure::formatTableStructureData($response['table_structure']);

        $i = 0;
        while ($csvLine = fgetcsv($handle)) {
            $k = 0;

            $insertData = array();
            foreach ($request->mappingValue as $value) {
                if ($value != "") {                    
                    $insertData[$value] = $csvLine[$k];
                }
                $k++;
            }
            
            $teamData = team_table_mapping::makeNewEntryInTable($table_name, $insertData, $table_structure);

            team_table_mapping::makeNewEntryForSource($table_incr_id, 'CSV_IMPORT');
            $this->insertActivityData($table_name, $teamData);

            $i++;
        }

        return response()->json(['Message' => 'Success', 'Status' => '200', 'Data' => new \stdClass()])->setStatusCode(200);
    }

    private function _performActions($tableId , $tableData , $recordId)
    {
        $tabsData = Tabs::getTabsDataByTableId($tableId);
        foreach($tabsData as $tabs)
        {
            $isAction = false;
            $query = json_decode($tabs->query);

            $queryCount = count((array)$query);
            
            $statusFlag = 0;

            $actionData = json_decode($tabs->action_value);
            
            foreach($query as $q)
            {
                foreach($q as $key=>$value)
                {
                    $condition = key((array)$value);
                    
                    if(isset($tableData[$key]) && $tableData[$key]!="")
                    {
                        switch($condition)
                        {
                            case "is":
                                if($tableData[$key]==$value->$condition)
                                {
                                    $statusFlag++;
                                }
                            break;
                            case "is_not":
                                if($tableData[$key]!=$value->$condition)
                                {
                                    $statusFlag++;
                                }
                            break;
                            case "starts_with":
                                $conditionLength = strlen($value->$condition);
                                if(substr($tableData[$key] , 0 , $conditionLength)==$value->$condition)
                                {
                                    $statusFlag++;
                                }
                            break;
                            case "ends_with":
                                $conditionLength = strlen($value->$condition);
                                if(substr($tableData[$key] , (strlen($tableData[$key])-$conditionLength) , strlen($tableData[$key]))==$value->$condition)
                                {
                                    $statusFlag++;
                                }
                            break;
                            case "contains":
                                if(strpos($tableData[$key] , $value->$condition) || strpos($tableData[$key] , $value->$condition)>(-1))
                                {
                                    $statusFlag++;
                                }
                            break;
                            case "not_contains":
                                if(!strpos($tableData[$key] , $value->$condition) || strpos($tableData[$key] , $value->$condition)<0)
                                {
                                    $statusFlag++;
                                }
                            break;
                            case "is_unknown":

                            break;
                            case "has_any_value":
                                if($tableData[$key]!="")
                                {
                                    $statusFlag++;
                                }
                            break;
                            case "less_than":
                                if($tableData[$key] < $value->$condition)
                                {
                                    $statusFlag++;
                                }
                            break;
                            case "greater_than":
                                if($tableData[$key]>$value->$condition)
                                {
                                    $statusFlag++;
                                }
                            break;
                            case "equals_to":
                                if($tableData[$key] == $value->$condition)
                                {
                                    $statusFlag++;
                                }
                            break;
                            case "days_after":
                                // Compare Date from date column
                                
                                $to = \Carbon\Carbon::createFromFormat('Y-m-d H:s:i', \Carbon\Carbon::now());
                                
                                $from = \Carbon\Carbon::createFromFormat('Y-m-d H:s:i', date('Y-m-d H:i:s' , strtotime($tableData[$key])));
                                

                                $diff_in_days = $to->diffInDays($from);
                                if($diff_in_days > $value->$condition)
                                {
                                    $statusFlag++;
                                }
                            break;
                            case "days_before":
                                // Compare Date from date column
                                $to = \Carbon\Carbon::createFromFormat('Y-m-d H:s:i', \Carbon\Carbon::now());
                                
                                $from = \Carbon\Carbon::createFromFormat('Y-m-d H:s:i', date('Y-m-d H:i:s' , strtotime($tableData[$key])));

                                $diff_in_days = $to->diffInDays($from);
                                if($diff_in_days < $value->$condition)
                                {
                                    $statusFlag++;
                                }
                            break;
                            case "between":
                                // Compare Date from date column
                                $to = \Carbon\Carbon::createFromFormat('Y-m-d H:s:i', \Carbon\Carbon::now());
                                
                                $from = \Carbon\Carbon::createFromFormat('Y-m-d H:s:i', date('Y-m-d H:i:s' , strtotime($tableData[$key])));

                                $diff_in_days = $to->diffInDays($from);
                                if($diff_in_days > $value->$condition->before && $diff_in_days < $value->$condition->after)
                                {
                                    $statusFlag++;
                                }
                            break;
                            case "after":
                                // Compare Date
                                $to = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $date('Y-m-d H:i:s' , strtotime($value->$condition)));
                                
                                $from = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s' , strtotime($tableData[$key])));
                                
                                if($to < $from)
                                {
                                    $statusFlag++;
                                }
                            break;
                            case "on":
                                // Compare Date
                                $to = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s' , strtotime($value->$condition)));
                                
                                $from = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s' , strtotime($tableData[$key])));
                                if($to == $from)
                                {
                                    $statusFlag++;
                                }
                            break;
                            case "before":
                                // Compare Date
                                $to = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s' , strtotime($value->$condition)));
                                
                                $from = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s' , strtotime($tableData[$key])));
                                if($to > $from)
                                {
                                    $statusFlag++;
                                }
                            break;
                        }
                    }
                }
            }

            // echo PHP_EOL;

            // echo "Status = ".$statusFlag."     Query Count = ".$queryCount;
            
            // echo PHP_EOL;
            
            // print_r($query);
            
            // echo PHP_EOL;

            if($statusFlag == $queryCount)
            {
                if(isset($actionData->MODIFY_COLUMN))
                {
                    if(isset($actionData->MODIFY_COLUMN->column_name))
                    {
                        $this->_modifyActionFunction($tableId , $recordId , $actionData);
                    }
                }
            }

            echo "-------------------------------------------------";
        }
    }
    private function _modifyActionFunction($tableId , $recordId , $actionData)
    {
        \DB::table($tableId)
            ->where('id', $recordId)
            ->update([$actionData->MODIFY_COLUMN->column_name => $actionData->MODIFY_COLUMN->value]);
    }
}
