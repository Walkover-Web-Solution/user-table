<?php

namespace App\Http\Controllers;

use App\Http\Helpers;
use Illuminate\Http\Request;
use App\ColumnType;
use App\team_table_mapping;
use App\TableStructure;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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

        TableStructure::updateStructureInBulk($resp['data']);

        $paramArr['socket_api'] = $request->input('socketApi');
        $paramArr['new_entry_api'] = $request->input('newEntryApi');
        team_table_mapping::updateTableStructure($paramArr, $tableAutoIncId);

        $tableName = $tableNames['table_id'];
        $logTableName = "log_" . $tableNames['table_name'] . "_" . $tableNames['team_id'];


        if (!empty($newTableStructure)) {
            try {
                Schema::table($tableName, function (Blueprint $table) use ($newTableStructure,$tableName) {
                    foreach ($newTableStructure as $value) {
                        $value['name'] = strtolower(preg_replace('/\s+/', '_', $value['name']));
                        if(Schema::hasColumn($tableName, $value['name'])) //check whether users table has email column
                        {
                            if ($value['unique'] == 'true') {
                                //$table->string($value['name'])->unique($value['name'])->change();
                            } else {
                                if($value['type']==9){
                                    $table->integer($value['name'])->unsigned()->nullable()->change();
                                }else {
                                    $table->string($value['name'])->nullable()->change();
                                }
                            }
                        }else{
                            if ($value['unique'] == 'true') {
                                $table->string($value['name'])->unique($value['name']);
                            } else {
                                if($value['type']==9){
                                    $table->integer($value['name'])->unsigned()->nullable();
                                }else {
                                    $table->string($value['name'])->nullable();
                                }
                            }
                        }

                    }
                });

                Schema::table($logTableName, function (Blueprint $table) use ($tableData) {
                    foreach ($tableData as $value) {
                        $value['name'] = strtolower(preg_replace('/\s+/', '_', $value['name']));
                        $table->string($value['name'])->nullable();
                    }
                });
            } catch (\Illuminate\Database\QueryException $ex) {
                $arr['msg'] = "Error in updation";
                $arr['exception'] = $ex;
                return response()->json($arr);
            }
        }

        $arr['msg'] = "Table Updated Successfuly";
        $arr['newTableStructure'] = $newTableStructure;

        return response()->json($arr);
    }
}
