<?php

namespace App\Http\Controllers;

use App\ColumnType;
use App\Http\Helpers;
use App\TableStructure;
use App\Tabs;
use App\Tables;
use App\team_table_mapping;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use DB;

class ConfigureTable extends Controller
{
    public function getOptionList() {
        return ColumnType::all()->toArray();
    }

    public function loadSelectedTableStructure($tableName)
    {
        $tableNames = team_table_mapping::getUserTablesNameById($tableName);
        $tableStructure = TableStructure::withColumns($tableNames['id']); // This data already come in above table

        $ColumnType = ColumnType::all();
        $new_arr = array();
        foreach ($tableNames['table_structure'] as $k => $v) {
            $new_arr[$v['column_name']] = $v;
        }
        
        return view('configureTable', array(
            'tableData' => $tableNames,
            'structure' => $tableStructure,
            'sequence' => $new_arr,
            'columnList' => $ColumnType));
    }
    public function listTableFilters($tableName){
        $tableNames = team_table_mapping::getUserTablesNameById($tableName);
        $tabData = Tabs::getTabsListByTableId($tableNames['table_id']);
        $tabs = json_decode(json_encode($tabData), true);
        $tabCount = Tables::getAllTabsCount($tableNames['table_id'], $tabs);
        
        return view('tableFilterList', array(
            'tableData' => $tableNames,
            'tabData' => $tabs,
            'tabCount' => $tabCount));
    }

    public function configureSelectedTable(Request $request)
    {
        $tableData = $request->input('tableData');
        $tableOldData = $request->input('tableOldData');
        if (!empty($tableData) && !empty($tableOldData))
            $newTableStructure = array_merge($tableData, $tableOldData);
        else if(empty($tableOldData))
            $newTableStructure = $tableData;
        else
            $newTableStructure = $tableOldData;

        $newTableStructure = Helpers::aasort($newTableStructure, "ordering");


        $tableId = $request->input('tableId');
        $tableNames = team_table_mapping::getUserTablesNameById($tableId);

        $tableAutoIncId = $tableNames['id'];
        $resp = TableStructure::validateStructure($newTableStructure, $tableAutoIncId);
        if (isset($resp['error'])) {
            return response()->json($resp);
        }

        $paramArr['socket_api'] = $request->input('socketApi');
        $paramArr['new_entry_api'] = $request->input('newEntryApi');
        team_table_mapping::updateTableStructure($paramArr, $tableAutoIncId);

        $tableName = $tableNames['table_id'];

        $arr = array();
        if (!empty($newTableStructure)) {
            Schema::table($tableName, function (Blueprint $table) use ($newTableStructure, &$resp) {
                foreach ($newTableStructure as $k => $value) {
                    if(isset($value['name_edit']) && $value['name_edit'] == 'true')
                    {
                        $table->renameColumn($value['old_name'], $value['name']);
                        $resp['data'][$k]['id'] = $value['id'];
                        $resp['data'][$k]['column_name'] = $value['name'];
                        TableStructure::updateStructureInBulk($resp['data'][$k]);
                    }
                }
            });
            Schema::table($tableName, function (Blueprint $table) use ($newTableStructure,$tableName,&$arr) {
                foreach ($newTableStructure as $value) {
                    $value['name'] = strtolower(preg_replace('/\s+/', '_', $value['name']));
                    if (Schema::hasColumn($tableName, $value['name'])) //check whether table has this column
                    {
                        $sm = Schema::getConnection()->getDoctrineSchemaManager();
                        $doctrineTable = $sm->listTableDetails($tableName);
                        if ($value['unique'] == 'true') {
                            $flag =$this->checkDuplicateValue($tableName, 'edit',$value['name']);
                            if($flag)
                            {
                                $arr['error'] = "Error in update. Duplicate entry for key '".$value['name']."'.";
                                break;
                            }
                            
                            if (! $doctrineTable->hasIndex($value['name'])) {
                                $table->string($value['name'])->unique($value['name'])->change();
                            } 
                        } else {
                            if ($doctrineTable->hasIndex($value['name'])) {
                                $table->dropIndex($value['name']);
                            }
                            if($value['type']==9){
                                $table->integer($value['name'])->unsigned()->nullable()->change();
                            } else if ($value['type'] == 11) {
                                $table->longText($value['name'])->nullable()->change();
                            }else if($value['type']==4){
                                $table->float($value['name'], 15, 2)->default(0)->change();
                            }else {
                                $table->string($value['name'])->nullable()->change();
                            }
                        }
                    }else{
                        if ($value['unique'] == 'true') {
                            $flag = $this->checkDuplicateValue($tableName, 'add',$value['name']);
                            if($flag)
                            {
                                $arr['error'] = "Error in create column. Duplicate entry for key '".$value['name']."'";
                                break;
                            } 
                            $table->string($value['name'])->unique($value['name']);
                        } else {
                            if($value['type']==9){
                                $table->integer($value['name'])->unsigned()->nullable();
                            } else if ($value['type'] == 11) {
                                $table->longText($value['name'])->nullable();
                            }else if($value['type']==4){
                                $table->float($value['name'], 15, 2)->default(0);
                            }else {
                                $table->string($value['name'])->nullable();
                            }
                        }
                    }
                }
            });
        }
        
        if(!isset($arr['error'])){
            TableStructure::updateStructureInBulk($resp['data']);
            $arr['msg'] = "Table Updated Successfuly";
            $arr['newTableStructure'] = $newTableStructure;
        }
        return response()->json($arr);
    }
    public function checkDuplicateValue($tablename, $type,$columnName)
    {
        if($type == 'add')
        {
            $count = DB::table($tablename)->selectRaw('*')->count();
            if($count>1)
                return true;
            else {
                return false;
            }
        }else if($type == 'edit'){
            $rows = DB::table($tablename)->select($columnName, DB::raw('COUNT(*) as `count`'))
            ->groupBy($columnName)
            ->having('count', '>', 1)->get()->toArray();
            $count = count($rows);
            if($count)
                return true;
            else {
                return false;
            }
        }else{
            return true;
        }
    }

    public function hideTableColumn(Request $request)
    {
        $tableId = $request->input('id');
        $columnname = $request->input('columnname');
        if(empty($tableId)){
            return response()->json(array('error' => 'Table id does not blank.'));
        }
        if(empty($columnname))
        {
            return response()->json(array('error' => 'Table column name does not blank.'));
        }
        
        $tableStructure = TableStructure::getTableStructure($tableId);
        if(empty($tableStructure))
        {
            return response()->json(array('error' => 'Table id does not exit.'));
        }
        //echo $id;echo $columnname;die;
        TableStructure::updateTableStructureColumnByTableId($tableId, $columnname, 'display', 0);
        return response()->json(array('success'=>'column updated successfully'));
    }
    
    public function getTableColumnDetails(Request $request)
    {
        $tableId = $request->input('tableid');
        $columnName = $request->input('columnname');
        if(empty($tableId)){
            return response()->json(array('error' => 'Table id does not blank.'));
        }
        if(empty($columnName))
        {
            return response()->json(array('error' => 'Table column name does not blank.'));
        }
        
        $tableStructure = TableStructure::getTableColumnStructure($tableId, $columnName);
        if(empty($tableStructure))
        {
            return response()->json(array('error' => 'Table column id does not exit.'));
        }
        return response()->json($tableStructure);
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
