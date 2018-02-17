<?php

namespace App\Http\Controllers;

use App\ColumnType;
use App\Http\Helpers;
use App\TableStructure;
use App\team_table_mapping;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class TableAccess extends Controller
{
    public function getOptionList() {
        return ColumnType::all()->toArray();
    }

    public function configureTableAccess($tableName)
    {
        $tableNames = team_table_mapping::getUserTablesNameById($tableName);
        $tableStructure = TableStructure::withColumns($tableNames['id']); // This data already come in above table

        $tableExistingData =  team_table_mapping::getUserTablesNameByParentId($tableName);

       // print_r($tableExistingData);

        $ColumnType = ColumnType::all();
        $new_arr = array();
        foreach ($tableNames['table_structure'] as $k => $v) {
            $new_arr[$v['column_name']] = $v;
        }

        return view('configureTableAccess', array(
            'tableData' => $tableNames,
            'tableExistingData'=>  $tableExistingData,
            'structure' => $tableStructure,
            'sequence' => $new_arr,
            'columnList' => $ColumnType));
    }

    public function manageTableAccess(Request $request)
    {
        $tableExistingData = $request->input('tableExistingData');
        $tableNewData = $request->input('tableNewData');
        $tableId = $request->input('tableId');
        $tableNames = team_table_mapping::getUserTablesNameById($tableId);
        $newdata = array();
        if(is_array($tableExistingData)) {
            foreach ($tableExistingData as $k => $v) {  
                $data = array(
                    'table_name'=> $tableNames['table_name'],
                    'table_id'=>$tableNames['table_id'],
                    'team_id'=>$v['email'],
                    'table_structure'=> json_encode($v['columns']),
                    'parent_table_id'=> $tableId ,
                );
                $newData = team_table_mapping::updateTableStructure($data,$v['id']);
            }
        }
        
        if(is_array($tableNewData)) {
            foreach ($tableNewData as $k => $v) {  
                $data = array(
                    'table_name'=> $tableNames['table_name'],
                    'table_id'=>$tableNames['table_id'],
                    'team_id'=>$v['email'],
                    'table_structure'=> json_encode($v['columns']),
                    'parent_table_id'=> $tableId ,
                );
                $newData = team_table_mapping::makeNewTableEntry($data);
            }
        }

        $arr['msg'] = "Table Updated Successfuly";
        return response()->json($arr);
    }

    public function addNewDropDownValue(Request $request)
    {
        $tableId = $request->input('tableId');
        $columnName = $request->input('name');
        $newVal = $request->input('value');
        $tableStructure = TableStructure::getTableColumnStructure($tableId,$columnName);
        $oldValue = json_decode($tableStructure['default_value'],true);
        $options = $oldValue['options'];
        $newValue = json_encode(array('options'=>array_merge($options,array($newVal))));
        return TableStructure::updateTableStructureColumn($tableStructure['id'],'default_value',$newValue);
    }
}
