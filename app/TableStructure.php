<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\ColumnType;
use Yadakhov\InsertOnDuplicateKey;

class TableStructure extends Model {

    // The function is implemented as a trait.
    use InsertOnDuplicateKey;
    protected $hidden = ['created_at', 'updated_at'];

    public function columnType()
    {
        return $this->belongsTo(ColumnType::class,'column_type_id','id');
    }

    public static function withColumns($tableId) {
        return TableStructure::with('columnType')->where('table_id',$tableId)->get()->toArray();
    }

    public static function withColumnTypes($tableId){
        return TableStructure::select('table_structures.column_name','column_types.column_name as coltype')
            ->join('column_types','column_type_id','=','column_types.id')
            ->where('table_id',$tableId)->get()->toArray();
    }

    public static function getTableColumnStructure($tableId,$columnName){
        return TableStructure::with('columnType')
            ->where('table_id',$tableId)
            ->where('column_name',$columnName)
            ->first()->toArray();
    }

    public static function getTableStructure($tableId){
        return TableStructure::select("*")
            ->where('table_id',$tableId)
            ->get()->toArray();
    }

    public static function updateTableStructureColumn($id,$name,$value)
    {
        return TableStructure::where('id', $id)
            ->update(array($name => $value));
    }
    
    public static function updateTableStructureColumnByTableId($tableId,$columnName,$name,$value)
    {
        return TableStructure::where('table_id', $tableId)
                ->where('column_name',$columnName)
            ->update(array($name => $value));
    }

    public static function getTableColumnTypesArray($user_table_id){
        $dt = team_table_mapping::getUserTableByTableId($user_table_id);
            $table_id = $dt['id'];
            $coltypes = TableStructure::withColumnTypes($table_id);
            $arrColType = [];
            foreach($coltypes as $col){
                $arrColType[$col['column_name']] = $col['coltype'];
            }
        return $arrColType;    
    }

    public static function insertTableStructure($tableStructure) {
        TableStructure::insert($tableStructure);
    }

    public static function deleteTableStructure($id) {
        TableStructure::where('table_id', $id)->delete();
    }

    public static function updateStructureInBulk($tableStructure){
        TableStructure::insertOnDuplicateKey($tableStructure);
    }

    public static function validateStructure($tableData, $tableAutoIncId = 0)
    {
        $tableStructure = array();
        foreach ($tableData as $key => $value)
        {
            $value['name'] = strtolower(preg_replace('/\s+/', '_', $value['name']));

            if (empty($value['name']))
            {
                $arr['msg'] = "Name Can't be empty";
                $arr['error'] = TRUE;
                return $arr;
            }

            if (empty($value['type']))
            {
                $arr['msg'] = "type Can't be empty";
                $arr['error'] = TRUE;
                return $arr;
            }

            $defaultValeArray = explode(',', $value['value']);

            if(!empty($defaultValeArray))
                $arr_tojson = json_encode(array('options'=>$defaultValeArray));
            else{
                $arr_tojson='';
            }

            if(!empty($value['unique']) && $value['unique']=='false')
            {
                $value['unique']=0;
            }
            if(isset($value['display']) && $value['display']==1){
                $value['display']=1;
            }else{
                $value['display']=0;
            }

            if ($tableAutoIncId)
            {
                $tableStructure[] = array(
                    'table_id' => $tableAutoIncId,
                    'column_name' => $value['name'],
                    'column_type_id' => $value['type'],
                    'default_value' => $arr_tojson,
                    'ordering' => $value['ordering'],
                    'display' => $value['display'],
                    'is_unique' => (empty($value['unique']) || $value['unique']==false) ? 0 : 1,
                    'created_at' => Carbon::now()->toDateTimeString(),
                    'updated_at' => Carbon::now()->toDateTimeString()
                );
            }
            else
            {
                $tableStructure[] = array(
                    'column_name' => $value['name'],
                    'column_type_id' => $value['type'],
                    'default_value' => $arr_tojson,
                    'ordering' => $value['ordering'],
                    'display' => $value['display'],
                    'is_unique' => empty($value['unique']) ? 0 : 1,
                    'created_at' => Carbon::now()->toDateTimeString(),
                    'updated_at' => Carbon::now()->toDateTimeString()
                );
            }
        }
        return array(
            'success' => TRUE,
            'data' => $tableStructure
        );
    }

    public static function formatTableStructureData($tableStructure){
        $userTableStructure = array();
        foreach ($tableStructure as $detail) {
            $columnType = $detail['column_type'];
            $userTableStructure[$detail['column_name']] = array(
                'type' => $columnType['column_name'],
                'column_type_id' => $columnType['id'],
                'unique' => $detail['is_unique'],
                'value' => $detail['default_value'],
                'display' => $detail['display'],
                'value_arr' => json_decode($detail['default_value'],TRUE)
                );
        }
        return $userTableStructure;
    }
}
