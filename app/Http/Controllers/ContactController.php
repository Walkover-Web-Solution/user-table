<?php

namespace App\Http\Controllers;

use App\Entity\Contact;
use App\Repositories\TableDetailRepositoryInterface;
use App\Teams;

class ContactController extends Controller
{
    protected $contact;
    protected $tableDetail;

    public function __construct(TableDetailRepositoryInterface $tableDetail)
    {
        $this->tableDetail = $tableDetail;
    }

    public function show($tableId, $id)
    {
        $table = $this->tableDetail->get($tableId);
        $structure = $this->formatStructure($table->tableStructure);
        $this->contact = new Contact($table->table_id);
        $data = $this->contact->getContactById($id);
        $newData = json_decode(json_encode($data), true);
        foreach($table->tableStructure as $k=>$v)
        {
            $inner_ordered[$v['column_name']] = $newData[$v['column_name']];
        }

        $inner_ordered['id'] = $newData['id'];

        return response(
            json_encode(
                array('data' => $inner_ordered, 'colDetails' => $structure,
                    'authKey' => $table->auth)
            ), 200
        )->header('Content-Type', 'application/json');
    }

    public function formatStructure($structure)
    {
        $userTableStructure = array();
        foreach ($structure as $detail) {
            $columnType = $detail->columnName;
            $userTableStructure[$detail['column_name']] = array(
                'type' => $columnType->column_name,
                'column_type_id' => $columnType->id,
                'unique' => $detail->is_unique,
                'value' => $detail->default_value,
                'value_arr' => json_decode($detail->default_value, TRUE)
            );
        }
        return $userTableStructure;
    }
}