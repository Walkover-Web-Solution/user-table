<?php

namespace App\Http\Controllers;

use Exception;
use App\Tabs;
use App\Tables;
use App\Teams;
use App\team_table_mapping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use App\TableStructure;
use App\Viasocket;
use App\Http\Helpers;

class TableController extends Controller
{

    public function createTable(Request $request)
    {
        $randomAuth = str_random(15);
        $data1 = $request->input('tableData');
        $data = Helpers::aasort($data1, "ordering"); // Array sort by abhishek jain

        $resp = TableStructure::validateStructure($data);

        if (!empty($resp['error'])) {
            return response()->json($resp);
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
            Tables::createLogTable($logTableName, $data);

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

    public function getUserAllTables()
    {
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
            $allTabsData = json_decode(json_encode($allTabs), true);
            $orderNeed = Helpers::orderData($tableNames);
            array_unshift($orderNeed, 'id');

            if (!empty($allTabsData))
                $allTabsData = Helpers::orderArray($allTabsData, $orderNeed);

            $data = Tabs::getTabsByTableId($tableId);
            $tabs = json_decode(json_encode($data), true);

            $teamId = $tableNames['team_id'];
            $teammates = Teams::getTeamMembers($teamId);
            $teammatesoptions = array();
            foreach ($teammates as $tkey => $tvalue) {
                $teammatesoptions[] = $tvalue['name'];
            }
            $filters = Tables::getFiltrableData($tableId, $userTableStructure, $teammatesoptions);
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

            rsort($allTabsData);
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

    public function loadSelectedTableFilterData($tableId, $tabName)
    {
        $results = $this->processTableData($tableId, $tabName);
        return view('home', $results);
    }

    public function loadContacts($tableIdMain, $tabName)
    {
        $tabDataJson = Tables::TabDataBySavedFilter($tableIdMain, $tabName);
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
            $allTabs = \DB::table($tableIdMain)
                ->select('*')
                ->get();
            $orderNeed = Helpers::orderData($tableNames);
            array_unshift($orderNeed, 'id');
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
            if (!empty($tabData))
                $tabData = Helpers::orderArray($tabData, $orderNeed);

            $teamId = $tableNames['team_id'];
            $teammates = Teams::getTeamMembers($teamId);

            $teammatesoptions = array();
            foreach ($teammates as $tkey => $tvalue) {
                $teammatesoptions[] = $tvalue['name'];
            }
            $filters = Tables::getFiltrableData($tableIdMain, $userTableStructure, $teammatesoptions);
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

    public function processFilterData($req, $tableId, $pageSize = 20)
    {
        $tableNames = team_table_mapping::getUserTablesNameById($tableId);
        $userTableStructure = TableStructure::formatTableStructureData($tableNames['table_structure']);

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
            'pagination' => $data,
            'structure' => $userTableStructure,
        );
    }

    # function get search for selected filters

    public function applyFilters(Request $request)
    {
        $req = (array)($request->filter);
        $coltype = ($request->coltype);

        $tableId = $request->tableId;
        $responseArray = $this->processFilterData($req, $tableId, 30);
        if (request()->wantsJson()) {
            return response(json_encode(array('body' => $responseArray)), 400)
                ->header('Content-Type', 'application/json');
        } else {
            return view('table.response', $responseArray);
        }
    }

    public static function getAppliedFiltersData($req, $tableId, $pageSize = 20)
    {
        //  print_r($req);
        //  return;
        $users = \DB::table($tableId)->selectRaw('*');
        foreach (array_keys($req) as $paramName) {

            if (isset($req[$paramName]['is'])) {
                $users->where($paramName, '=', $req[$paramName]['is']);
            } else if (isset($req[$paramName]['is_not'])) {
                $users->where($paramName, '<>', $req[$paramName]['is_not']);
            } else if (isset($req[$paramName]['starts_with'])) {
                $users->where($paramName, 'LIKE', '' . $req[$paramName]['starts_with'] . '%');
            } else if (isset($req[$paramName]['ends_with'])) {
                $users->where($paramName, 'LIKE', '%' . $req[$paramName]['ends_with'] . '');
            } else if (isset($req[$paramName]['contains'])) {
                $users->where($paramName, 'LIKE', '%' . $req[$paramName]['contains'] . '%');
            } else if (isset($req[$paramName]['not_contains'])) {
                $users->where($paramName, 'LIKE', '%' . $req[$paramName]['not_contains'] . '%');
            } else if (isset($req[$paramName]['is_unknown'])) {
                $users->whereNull($paramName)->orWhere($paramName, '');
            } else if (isset($req[$paramName]['has_any_value'])) {
                $users->whereNotNull($paramName)->where($paramName, '<>', '');
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
            } else if (isset($req[$paramName]['to'])) {
                $users->where($paramName, '<=', $req[$paramName]['to']);
            } else if (isset($req[$paramName]['before'])) {
                $users->where($paramName, '<=', $req[$paramName]['to']);
            } else if (isset($req[$paramName]['after'])) {
                $users->where($paramName, '>=', $req[$paramName]['to']);
            }

        }
        $data = $users->paginate($pageSize);

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
                $user = \Auth::user();
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
                $arr['teamData'] = $teamData;
                $arr['user'] = $user;
                return response()->json($arr);
            }
        } catch (Exception $ex) {
            $arr['msg'] = "Error occurred";
            $arr['exception'] = $ex->getMessage();
            return response()->json($arr);
        }
    }

    public function getSelectedTableStructure($tableName, Request $request)
    {
        $tableNames = team_table_mapping::getUserTablesNameById($tableName);
        $tableNameArr = json_decode(json_encode($tableNames), true);
        $tableStructure = TableStructure::withColumns($tableNameArr['id']);
        $colDetails = TableStructure::formatTableStructureData($tableNames['table_structure']);
        $teamId = $tableNames['team_id'];
        $teammates = Teams::getTeamMembers($teamId);

        return response()->json(array(
            'tableData' => $tableNameArr,
            'structure' => $tableStructure,
            'colDetails' => $colDetails,
            'teammates' => $teammates
        ));
    }

    public function updateEntry(Request $request)
    {

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

    public function getSearchedData($tableId, $query)
    {
        $array = $this->getTableSearchData($tableId, $query, 30);
        return view('table.response', $array);
    }

    public function getTableSearchData($tableId, $query, $pageSize = 20)
    {
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
        return $this->processFilterData($req, $tableDetails['id'], $pageSize);
    }

    public function getContacts(Request $request)
    {
        $tableDetails = $this->getTableDetailsByAuth($request->header('Auth-Key'));
        $pageSize = empty($request->get('pageSize')) ? 100 : $request->get('pageSize');
        $tabName = empty($request->get('filter')) ? 'All' : $request->get('filter');
        return $this->loadContacts($tableDetails['table_id'], $tabName);
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
