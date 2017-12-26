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
        $where = "";
        if(!empty($startDate) && !empty($endDate)){
            //$where = "WHERE STR_TO_DATE(FROM_UNIXTIME($dateColumn), '%d/%m/%Y') between STR_TO_DATE('$startDate', '%d/%m/%Y') AND STR_TO_DATE('$endDate', '%d/%m/%Y')";
            $starttime = strtotime($startDate);
            $endtime = strtotime($endDate);
            $where = "WHERE $dateColumn between $starttime AND $endtime";
        }

        if($column_type == 'date')
            $sql = "SELECT from_unixtime($secondColumn, '%Y-%d-%m') LabelColumn,Count($secondColumn) as Total FROM $userTableName $where group by from_unixtime($secondColumn, '%Y-%d-%m')";
        else
            $sql = "SELECT $secondColumn LabelColumn,Count($secondColumn) as Total FROM $userTableName $where group by $secondColumn";

        $tableData = Tables::getSQLData($sql);
        return json_encode($tableData);
    } 

    public function showGraphForTable($tableName) {
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
                if(($total - $allrecords) > 5 )
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

            $arrTabCount = Tables::getAllTabsCount($tableId, $tabs);
            $allTabCount = Tables::getCountOfTabsData($tableId, "All");
            $d=strtotime("-3 days");
            $rangeStart = date('m/d/Y',$d);
            $d1=strtotime("+3 days");
            $rangeEnd = date('m/d/Y',$d1);
            return view('graph', array(
                'activeTab' => 'All',
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
