<?php

namespace App\Http\Controllers;


use App\Repositories\TableDetailRepositoryInterface;
use App\Entity\Activity;
use App\User;
//use Carbon\Carbon;

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
            
            if(strtolower($act->action) == 'create')
                $carbonDate = $act->created_at;
            else
                $carbonDate = $act->updated_at;
            
            $userData = "Guest User ";

            $userDetails = User::where('email',$act->user_id)->first();
            
            if($userDetails)
                $userData = $userDetails->first_name." ".$userDetails->last_name;
            
            $date = $carbonDate->diffForHumans();
            $act->activityDate = $date;
            $act->userName = $userData;
            $activityData[$key] = $act;
        }

        return response(
            json_encode(
                array('data'=>$activity)
            ), 200
        )->header('Content-Type', 'application/json');
    }
}
