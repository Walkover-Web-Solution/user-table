<?php

namespace App\Http\Controllers;


use App\Repositories\TableDetailRepositoryInterface;
use App\Entity\Activity;

class ActivityController extends Controller
{
    protected $activity;
    protected $tableDetail;

    public function __construct(TableDetailRepositoryInterface $tableDetail)
    {
        $this->tableDetail = $tableDetail;
    }

    public function show($table_id, $content_id)
    {
        $table = $this->tableDetail->get($table_id);

        $log_table = 'log' . substr($table->table_id, 4);
        $this->activity = new Activity($log_table);

        $activity = $this->activity->getByContentId($content_id);
        $activityData = array();
        foreach ($activity as $key=>$act){
            $log = $this->activity->getDescription($act);
            $act->log = $log;
            $activityData[$key] = $act;
        }

        return response(
            json_encode(
                array('data'=>$activity)
            ), 200
        )->header('Content-Type', 'application/json');
    }
}
