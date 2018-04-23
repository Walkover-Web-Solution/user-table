<?php

namespace App\Http\Controllers;

use App\Repositories\TableDetailRepositoryInterface;
use App\Teams;

class TeamController extends Controller
{
    protected $contact;
    protected $tableDetail;

    public function __construct(TableDetailRepositoryInterface $tableDetail)
    {
        $this->tableDetail = $tableDetail;
    }

    public function list($tableId)
    {
        $table = $this->tableDetail->get($tableId);
        $parentTableId = $table->parent_table_id;
        if(!empty($parentTableId))
            $table = $this->tableDetail->get($parentTableId);
        $teamId = $table->team_id;
        $teammates = Teams::getTeamMembers($teamId);

        return response(
            json_encode(
                array('data' => $teammates)
            ), 200
        )->header('Content-Type', 'application/json');
    }

}