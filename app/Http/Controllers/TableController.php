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
//        $this->middleware('auth');
    }
    
    public function createTable(Request $request) {
        $randomAuth = str_random(15);
        $data = $request->input('tableData');
        $structureDataAr = array();
        foreach($data as $key => $value){
            $structureDataAr[$value['name']] = array('type' => $value['type'],'unique' => $value['unique'],'value' => $value['value']); 
        }
        $structureDataJson = json_encode($structureDataAr);
        $userTableName = $request->input('tableName');
        $teamId = $request->input('teamId');
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
        
        foreach($teams as $teamId => $teamName){
            $teamNameArr[] = $teamName;
            $teamIdArr[] = $teamId;
        }
        session()->put('teamNames', $teamNameArr);
        session()->put('teams', $teams);
        
        $tableLst = $this->getUserTablesByTeamId($teamIdArr);
        $table_incr_id_arr = array();
        
        foreach($tableLst as $key=>$value){
            $table_incr_id_arr[] = $value['id'];            
        }
        $data = json_decode(json_encode(team_table_mapping::getTableSourcesByTableIncrId($table_incr_id_arr)),true);
        
        $source_arr = array();
        foreach($data as $key =>$value){
            $source_arr[$value['table_incr_id']][] = $value['source'];
        }
        
        return view('showTable', array(
                'allTables' => $tableLst,
                'teamsArr' => $teams,
                'source_arr' => $source_arr
        ));
        
    }
    
    public function getAllTablesForSocket(Request $request){
        
        $team_ids = $request->input('team_ids');
        $team_id_array = explode(',', $team_ids);
        $table_data = $this->getUserTablesByTeamId($team_id_array);
        $table_array = array();
        $count = 0;
        foreach($table_data as $key=>$value){
            $table_array[$value['team_id']][$count]['table_id'] = $value['table_id'];
            $table_array[$value['team_id']][$count]['table_name'] = $value['table_name'];
            $table_array[$value['team_id']][$count]['structure'] = $value['table_structure'];
            $table_array[$value['team_id']][$count]['auth'] = $value['auth'];
            $count++;
        }
        $response_arr = array();
        $count = 0;
        foreach($table_array as $team_id => $table_data){
            $response_arr[$count]['team_id'] = $team_id;
            $response_arr[$count]['tables'] = $table_data;
            $count++;
            
        }
        return response()->json($response_arr);
    }
    function getUserTablesByTeamId($teamIdArr){
        $tableLst = team_table_mapping::getUserTablesByTeam($teamIdArr);
        $tableLst = json_decode(json_encode($tableLst), true);
        return $tableLst;
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

			if(!empty($data))
			{
				foreach($data as $val)
				{
					 $tab_name = $val['tab_name'];
					 $tabCount = Tables::TabDataBySavedFilter($tableId, $tab_name);
					 $tabCount = count($tabCount);
					 //if(empty($tabCount))
					 $arrTabCount[] = array($tab_name => $tabCount);			 
				}
		    }
			else
			{
                     $arrTabCount = array();
			}
			$allTabCount = count($allTabs);
              
            return view('home', array(
                'activeTab' => 'All',
                'tabs' => $data,
                'allTabs' => $allTabs,
				'allTabCount'=> $allTabCount,
				'arrTabCount' => $arrTabCount,
                'tableId' => $tableName,
                'userTableName' => $userTableName,
                'filters' => $filters,
                'structure' => $userTableStructure));
        }
    }
	
	
	
public static function dateDiff1(Request $request)
{
  
      $date = $request->date;
// echo $mydate_new= date("Y-m-d H:i:s");
 
  $mydate= date("Y-m-d H:i:s");
  
  $theDiff="";
  //echo $mydate;//2014-06-06 21:35:55 
  
 $datetime1 = date_create($date); 

  $datetime2 = date_create($mydate);
  
  $interval = date_diff($datetime1, $datetime2);
  //echo $interval->format('%s Seconds %i Minutes %h Hours %d days %m Months %y Year    Ago')."<br>";
  $min=$interval->format('%i');
  $sec=$interval->format('%s');
  $hour=$interval->format('%h');
  $mon=$interval->format('%m');
  $day=$interval->format('%d');
  $year=$interval->format('%y');
  if($interval->format('%i%h%d%m%y')=="00000")
  {
    //echo $interval->format('%i%h%d%m%y')."<br>";
	if($sec == 1)
    return $sec." Second";
	else
	return $sec." Seconds";

  } 

else if($interval->format('%h%d%m%y')=="0000"){
  if($min == 1)
  return $min." Minute";
  else
   return $min." Minutes";
   }


else if($interval->format('%d%m%y')=="000"){
   if($hour == 1)
   return $hour." Hour";
   else
   return $hour." Hours";
   }


else if($interval->format('%m%y')=="00"){
    if($day == 1)
   return $day." Day";
   else
   return $day." Days";
   }

else if($interval->format('%y')=="0"){
     
	  if($mon == 1)
	 return $mon." Month";
	 else
	 return $mon." Months";
   }

else{
       if($mon == 1)
	  return $year." Year";
	  else
	   return $year." Years";
   }

}

public static function dateDiff($ts)
{
    if(!ctype_digit($ts))
        $ts = strtotime($ts);

    $diff = time() - $ts;
    if($diff == 0)
        return 'now';
    elseif($diff > 0)
    {
        $day_diff = floor($diff / 86400);
        if($day_diff == 0)
        {
            if($diff < 60) return 'just now';
            if($diff < 120) return '1 minute ago';
            if($diff < 3600) return floor($diff / 60) . ' minutes ago';
            if($diff < 7200) return '1 hour ago';
            if($diff < 86400) return floor($diff / 3600) . ' hours ago';
        }
        if($day_diff == 1) return 'Yesterday';
        if($day_diff < 7) return $day_diff . ' days ago';
        if($day_diff < 31) return ceil($day_diff / 7) . ' weeks ago';
        if($day_diff < 60) return 'last month';
        return date('F Y', $ts);
    }
    else
    {
        $diff = abs($diff);
        $day_diff = floor($diff / 86400);
        if($day_diff == 0)
        {
            if($diff < 120) return 'in a minute';
            if($diff < 3600) return 'in ' . floor($diff / 60) . ' minutes';
            if($diff < 7200) return 'in an hour';
            if($diff < 86400) return 'in ' . floor($diff / 3600) . ' hours';
        }
        if($day_diff == 1) return 'Tomorrow';
        if($day_diff < 4) return date('l', $ts);
        if($day_diff < 7 + (7 - date('w'))) return 'next week';
        if(ceil($day_diff / 7) < 4) return 'in ' . ceil($day_diff / 7) . ' weeks';
        if(date('n', $ts) == date('n') + 1) return 'next month';
        return date('F Y', $ts);
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
            if($tabName == "All")
            {
                $tabArray = array();
            }
            else{
                $tabSql = Tabs::where([['tab_name', $tabName],['table_id', $tableIdMain]])->first(['query'])->toArray();
                $tabArray = json_decode($tabSql['query'],true);
            }
            
            $tabData = Tables::TabDataBySavedFilter($tableIdMain, $tabName);
         
			
			foreach($tabData as $val)
				{
					
					$mydate = $val->date;
					//$mydate = '2017-11-25 14:01:53';
			        $datedemo = $this->dateDiff($mydate);
					//$val->date = $datedemo;
					$val->date = array($datedemo,$mydate);

				}
		
			$tabData = json_decode(json_encode($tabData), true);
			
            $filters = Tables::getFiltrableData($tableIdMain);
            
			if(!empty($data))
			{
			foreach($data as $val)
				{
					 $tab_name = $val['tab_name'];
					 $tabCount = Tables::TabDataBySavedFilter($tableIdMain, $tab_name);
					 $tabCount = count($tabCount);
					 $arrTabCount[] = array( $tab_name => $tabCount);
								 
				}
			}
			else
			{
                     $arrTabCount = array();
			}
			
			$allTabscount = \DB::table($tableIdMain)
                    ->select('*')
                    ->get();
            $allTabscount = json_decode(json_encode($allTabscount), true); 						
			$allTabCount = count($allTabscount);

            
			
			
			//die;
            //print_r($filters);die;
            return view('home', array(
                'activeTab' => $tabName,
                'tabs' => $data,
                'allTabs' => $tabData,
				'allTabCount'=> $allTabCount,
                 'arrTabCount' => $arrTabCount,
                'tableId' => $tableId,
                'userTableName' => $userTableName,
                'filters' => $filters,
                'activeTabFilter'=>$tabArray));
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
        unset($incoming_data['_token']);
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
