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

        if (Schema::hasTable($tableName)) {
            if (!empty($tableData)) {
                try {
                    Schema::table($tableName, function (Blueprint $table) use ($tableData) {
                        foreach ($tableData as $value) {
                            $value['name'] = preg_replace('/\s+/', '_', $value['name']);
                            if ($value['unique'] == 'true') {
                                $table->string($value['name'])->unique($value['name'])->change();
                            } else {
                                if($value['type']==9){
                                    $table->timestamp($value['name'])->nullable()->change();
                                }else {
                                    $table->string($value['name'])->nullable()->change();
                                }
                            }
                        }
                    });

                    Schema::table($logTableName, function (Blueprint $table) use ($tableData) {
                        foreach ($tableData as $value) {
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
        } else {
            $arr['msg'] = "Table Not Found";
            return response()->json($arr);
        }
    }
}
