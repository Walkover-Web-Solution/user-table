<?php

namespace App\Http\Controllers;

use App\Entity\Activity;
use App\Repositories\TableDetailRepositoryInterface;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    protected $activity;
    protected $tableDetail;

    public function __construct(TableDetailRepositoryInterface $tableDetail)
    {
        $this->tableDetail = $tableDetail;
    }

    public function addLog($tableId,Request $request)
    {
        $incoming_data = $request->all();
        $table = $this->tableDetail->get($tableId);
        //$log_table = 'log' . substr($table->table_id, 4);
        $log_table = 'activity_log';
        $this->activity = new Activity($log_table);
        $activityData = $this->getActivityData($incoming_data);
        $this->activity->addActivity($activityData);
//
//        return response(
//            json_encode(
//                array('success', true)
//            ), 200
//        )->header('Content-Type', 'application/json');
    }

    /**
     * Create an activity log entry.
     *
     * @param  mixed $data
     * @return boolean
     */
    public function getActivityData($data = [])
    {
        // set the defaults from config
        $defaults = config('log.defaults');
        if (!is_array($defaults))
            $defaults = [];

        // if data is a string, create the array from the description
        if (is_string($data)) {
            $data = ['description' => $data];

            $description = strtolower($data['description']);

            if (substr($description, 0, 4) == "edit")
                $data['action'] = "Update";

            if (substr($description, 0, 6) == "update")
                $data['action'] = "Update";

            if (substr($description, 0, 6) == "delete")
                $data['action'] = "Delete";
        } else // otherwise convert it to an array if it is an object
        {
            if (is_object($data))
                $data = (array)$data;
        }

        // set the user ID
        if (config('log.auto_set_user_id') && !isset($data['userId'])) {
            $user = call_user_func(config('log.auth_method'));

            $data['userId'] = isset($user->id) ? $user->id : null;
        }

        // allow "updated" boolean to set action and replace activity text verbs with "Updated"
        if (isset($data['updated'])) {
            if ($data['updated']) {
                $data['action'] = "Update";

                $data['description'] = str_replace('Added', 'Updated', str_replace('Created', 'Updated', $data['description']));
                $data['description'] = str_replace('added', 'updated', str_replace('created', 'updated', $data['description']));
            } else {
                $data['action'] = "Create";
            }
        }

        // allow "deleted" boolean to set action and replace activity text verbs with "Deleted"
        if (isset($data['deleted']) && $data['deleted']) {
            $data['action'] = "Delete";

            $data['description'] = str_replace('Added', 'Deleted', str_replace('Created', 'Deleted', $data['description']));
            $data['description'] = str_replace('added', 'deleted', str_replace('created', 'deleted', $data['description']));
        }

        // set developer flag
        if (!isset($data['developer']) && !is_null(session('developer')))
            $data['developer'] = true;

//        // set IP address
//        if (!isset($data['ipAddress']))
//            $data['ipAddress'] = Request::getClientIp();

        // set user agent
        if (!isset($data['userAgent']))
            $data['userAgent'] = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'No User Agent';

        // set additional data and encode it as JSON if it is an array or an object
        if (isset($data['data']) && (is_array($data['data']) || is_object($data['data'])))
            $data['data'] = json_encode($data['data']);

        // format array keys to snake case for insertion into database
        $dataFormatted = [];
        foreach ($data as $key => $value) {
            $dataFormatted[snake_case($key)] = $value;
        }

        // merge defaults array with formatted data array
        $data = array_merge($defaults, $dataFormatted);

        // if language keys are being used and description / details are arrays, encode them in JSON
//        if (isset($data['language_key']) && $data['language_key']) {
//            if (is_array($data['description']) || is_object($data['description']))
//                $data['description'] = json_encode($data['description']);
//
//            if (isset($data['details']) && (is_array($data['details']) || is_object($data['details'])))
//                $data['details'] = json_encode($data['details']);
//        }

        return $data;
    }

}