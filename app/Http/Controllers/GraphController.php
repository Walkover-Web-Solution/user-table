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

class GraphController extends Controller {
    public function getGraphDataForTable(Request $request) {
        $tableName = $request->input('tableName');
        $dateColumn = $request->input('dateColumn');
        $secondColumn = $request->input('secondColumn');
        $tabName = $request->input('tabName');

        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');

        $tableNames = team_table_mapping::getUserTablesNameById($tableName);
        $userTableName = $tableNames['table_id'];

        $userTableStructure = $tableNames['table_structure'];
        $column_type = "text";

        foreach ($userTableStructure as $key => $value) {
            if($value['column_name'] == $secondColumn){
                $column_type = $value['column_type']['column_name'];
            }
        }

        if($column_type == 'date'){
            //$sql = "SELECT from_unixtime($secondColumn, '%Y-%d-%m') LabelColumn,Count($secondColumn) as Total FROM $userTableName $where group by from_unixtime($secondColumn, '%Y-%d-%m')";
            $sql = "from_unixtime($secondColumn, '%Y-%d-%m') LabelColumn,Count($secondColumn) as Total";
            $groupby = "from_unixtime($secondColumn, '%Y-%d-%m')";
        }
        else{
            //$sql = "SELECT $secondColumn LabelColumn,Count($secondColumn) as Total FROM $userTableName $where group by $secondColumn";
            $sql = "$secondColumn LabelColumn,Count($secondColumn) as Total";
            $groupby = $secondColumn;
        }

        $users = DB::table($userTableName)->selectRaw($sql)->groupBy(DB::raw($groupby));

        if(!empty($startDate) && !empty($endDate)){
            $starttime = strtotime($startDate);
            $endtime = strtotime($endDate);
            $users->where($dateColumn, '>=', $starttime)->where($dateColumn, '<=', $endtime);
        }
        if (!empty($tabName)) {
            $tabSql = Tabs::where([['tab_name', $tabName], ['table_id', $tableName]])->first(['query']);
            $req = (array)json_decode($tabSql->query); 
            $coltypes = TableStructure::getTableColumnTypesArray($tableId);
            $users = Tables::makeFilterQuery($req, $users, $coltypes);
        } 
        $tableData = $users->get();// Tables::getSQLData($sql);
        return json_encode($tableData);
    } 

    public function showGraphForTable($tableName,$tabName = 'All') {
        $tableNames = team_table_mapping::getUserTablesNameById($tableName);
        $actualTableName = $tableNames['table_id'];
        $userTableName = $tableNames['table_name'];
        $userTableStructure = $tableNames['table_structure'];
        $date_columns = array();
        $other_columns = array();
        foreach ($userTableStructure as $key => $value) {
            if ($value['column_type']['column_name'] == 'date')
                $date_columns[] = $value['column_name'];
            else if ($value['is_unique'] == "false"){
                $col = $value['column_name'];
                $sql = "SELECT count(distinct $col) allrecords,count($col) total  FROM $actualTableName";
                $tableData = Tables::getSQLData($sql);
                $allrecords = $tableData[0]->allrecords;
                $total = $tableData[0]->total;
                if(($allrecords != 1 && $allrecords != $total && ($total - $allrecords) > 5 ))
                    $other_columns[] =  $col;
            }
                
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

            $coltypes = TableStructure::getTableColumnTypesArray($tableId);

            $arrTabCount = Tables::getAllTabsCount($tableId, $tabs);
            $allTabCount = Tables::getCountOfTabsData($tableId, "All", $coltypes);
            $d=strtotime("-3 days");
            $rangeStart = date('m/d/Y',$d);
            $d1=strtotime("+3 days");
            $rangeEnd = date('m/d/Y',$d1);
            return view('graph', array(
                'activeTab' => $tabName,
                'date_columns' => $date_columns,
                'other_columns' => $other_columns,
                'tabs' => $tabs,
                'allTabs' => $allTabsData,
                'arrTabCount' => $arrTabCount,
                'allTabCount' => $allTabCount,
                'tableId' => $tableName,
                'userTableName' => $userTableName,
                'structure' => $userTableStructure,
                'rangeStart' => $rangeStart,
                'rangeEnd' => $rangeEnd
                ));
        }
    }
}
