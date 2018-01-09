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
        $teamId = $table->team_id;
        $teammates = Teams::getTeamMembers($teamId);

        return response(
            json_encode(
                array('data' => $teammates)
            ), 200
        )->header('Content-Type', 'application/json');
    }

}