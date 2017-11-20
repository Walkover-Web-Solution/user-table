<?php

namespace App\Http\Controllers;

use App\Classes\Utility;
use App\StoreTokens;
use App\Tabs;
use App\TabUsers;
use App\Users;
use App\team_table_mapping;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UserController extends Controller {

    public function __construct() {
//        $this->middleware('auth');
    }

    public function getSearchedData($tab, $query) {

        $data = Users::getSearchedData($tab, $query);
        if (request()->wantsJson()) {
            return ['body' => $data];
        } else {
            return view('table.response', array(
                'allTabs' => $data->toArray()
            ));
        }
    }

//    # create user
//    public function add(Request $request) {
//        //dd($request->userdata);
//        //$requestObj = json_decode($request->userdata);
//        $requestObj = $request->userdata;
//
//        $currentDate = date('Y-m-d H:i');
//        $errors = array();
//        if (!isset($requestObj['username']))
//            return response(json_encode(array('error' => 'username is required')), 400)->header('Content-Type', 'application/json');
//
//        $data = $requestObj;
//        $username = $requestObj['username'];
//
//        if (count($errors))
//            return response(json_encode(array('error' => $errors)), 400)->header('Content-Type', 'application/json');
//
//        $res = Users::updateOrCreate(
//                        ['username' => $username]
//                        , $data
//        );
//
//        // Remove password from response
//        unset($res['password']);
//
//
//        $lastInsertId = \DB::getPdo()->lastInsertId();
//
//        // send data to webhook on update
//        if (!$lastInsertId) {
//            $data = (array) Users::getUserDetails($username);
//            $status = Utility::postToWebhook($data);
//        }
//
//
//        return response(json_encode(array('message' => 'user added/updated successfully', 'data' => $res)), 200)->header('Content-Type', 'application/json');
//    }


    public function getDetailsOfId($id) {
        $data = Users::find($id);
        if (!$data)
            return response(json_encode(array('error' => 'invalid id')), 403)->header('Content-Type', 'application/json');

        return response(json_encode(array('message' => 'project added successfully', 'data' => $data)), 200)->header('Content-Type', 'application/json');
    }

    public function getDetailsOfUserById($tableId,$id) {
        //$tableId = $request->tableId;
        $tableNameArr = team_table_mapping::getUserTablesNameById($tableId);
        $tableNameArr = json_decode(json_encode($tableNameArr), true);
        //$userTableName = $tableNameArr[0]['table_name'];
        //print_r($tableNameArr[0]);die;
        if (empty($tableNameArr[0]['table_id'])) {
            echo "no table found";
            exit();
        } else {
            $data = \DB::table($tableNameArr[0]['table_id'])->selectRaw('*')->where('id',$id)->first();
        }
        //$data = Users::find($id);
        $colDetails = json_decode($tableNameArr[0]['table_structure'],true); //Users::getColumnDetails();
        //print_r($colDetails);die;
        return response(
                        json_encode(
                                array('data' => $data, 'colDetails' => $colDetails,'authKey'=>$tableNameArr[0]['auth'])
                        ), 200
                )->header('Content-Type', 'application/json');
    }

    # update details

    public function updateDetails(Request $request, $id) {

        $data = Users::find($id);
        if (!$data)
            return response(json_encode(array('error' => 'invalid id')), 403)->header('Content-Type', 'application/json');

        //$requestObj = json_decode($request->getContent());
        $requestObj = (object) $request->all();

        $curDateTime = date('Y-m-d H:i:s');

        $update = array(
            'username' => ( isset($requestObj->username) ? $requestObj->username : $data->username ),
            'password' => ( isset($requestObj->password) ? $requestObj->password : $data->password ),
            'firstname' => ( isset($requestObj->firstname) ? $requestObj->firstname : $data->firstname ),
            'lastname' => ( isset($requestObj->lastname) ? $requestObj->lastname : $data->lastname ),
            'address' => ( isset($requestObj->address) ? $requestObj->address : $data->address ),
            'email' => ( isset($requestObj->email) ? $requestObj->email : $data->email ),
            'contact' => ( isset($requestObj->contact) ? $requestObj->contact : $data->contact ),
            'salary' => ( isset($requestObj->salary) ? $requestObj->salary : $data->salary ),
            'updated_at' => $curDateTime
        );
        $data = Users::updateData($update, $id);
        $lastInsertId = DB::getPdo()->lastInsertId();
        $defaultTab = array(
            'tab_name' => 'All',
            'user_id' => $lastInsertId
        );

        if ($data)
            return response(json_encode(array('message' => 'details updated successfully')), 200)->header('Content-Type', 'application/json');
        else
            return response(json_encode(array('error' => 'invalid id')), 403)->header('Content-Type', 'application/json');
    }

    # apply filters

    public function applyFilters(Request $request) {
        $req = (array) ($request->filter);

        $tab = $request->tab;

        if (strcasecmp($tab, "All") == 0) {
            $data = Users::getAppliedFiltersData($req);
        } else {
            $data = Users::getFilteredUsersDetailsData($req);
        }
        if (request()->wantsJson()) {
            return response(json_encode(array('body' => $data)), 200)->header('Content-Type', 'application/json');
        } else {
            return view('table.response', array(
                'allTabs' => $data->toArray()
            ));
        }
    }

    public function saveFilter(Request $request) {
        //$appliedFilters = $request->filter;
        $messages = [
            'tab.required' => 'The tab field is required.',
            'filter.required' => 'The filter field is required.'
        ];
        $this->validate($request, [
            'tab' => 'required',
            'filter' => 'required'
                ], $messages);
        $tab = $request->tab;
        $tableId = $request->tableId;

        if ((strcasecmp($tab, "All") == 0) || (strcasecmp($tab, "my-leads") == 0)) {
            return response(
                            json_encode(
                                    array('error' => 'All/My-Leads is not editable'
                                    ), JSON_UNESCAPED_SLASHES
                            ), 401
                    )->header('Content-Type', 'application/json');
        }
        if(empty($tableId)){
            return response(json_encode(array('error' => "something went wrong.")), 403)->header('Content-Type', 'application/json');
        }
        else{
            $tableNameArr = team_table_mapping::getUserTablesNameById($tableId);
            $tableNameArr = json_decode( json_encode($tableNameArr), true);
            if(empty($tableNameArr[0]['table_id'])){
                return response(json_encode(array('error' => "something went wrong.")), 403)->header('Content-Type', 'application/json');
            }
            else{
                $tableId = $tableNameArr[0]['table_id'];
            }
        }
        $appliedFilters = json_decode($request->filter);
        $save = array(
            'tab_name' => $tab,
            'query' => json_encode($appliedFilters, JSON_UNESCAPED_SLASHES),
            'table_id' => $tableId
        );
        $data = Tabs::updateOrCreate(
                        ['tab_name' => $tab]
                        , $save);
        if ($data)
            return response(json_encode(array('message' => $tab . ' saved successfully')), 200)->header('Content-Type', 'application/json');
        else
            return response(json_encode(array('error' => "something went wrong.")), 403)->header('Content-Type', 'application/json');
    }

    public function getFilters($tab) {
        $data = Users::getFiltrableData($tab);

        return response(json_encode(array('body' => $data)), 200)->header('Content-Type', 'application/json');
    }

    # to start crone job

    public function startJob() {
        $allTabs = Tabs::tabsWithWebhookUrls();  // get all the tabs

        foreach ($allTabs as $tab) {
            $currentUsersList = (array) Tabs::getListOfUsers($tab);
            $oldUsersList = (array) TabUsers::getOldUsersList($tab);
            $curDateTime = date('Y-m-d H:i:s');

            if (isset($currentUsersList['users_list']))
                $currentUsersList = explode(',', $currentUsersList['users_list']);
            if (isset($oldUsersList['users_list']))
                $oldUsersList = explode(',', $oldUsersList['users_list']);

            $newUsrsList = array_diff($currentUsersList, $oldUsersList);

            $newDetails = array();
            foreach ($newUsrsList as $newUser) {
                $userData = (array) Users::getUserDetails($newUser);
                //Utility::sendToWebhook($tab,$userData);
                array_push($newDetails, $userData);
            }
            //$newDetails =  json_encode($newDetails);
            // print_r($newDetails);
            if (count($newDetails)) {
                Utility::sendToWebhook($tab, $newDetails);

                //update the tabs_users list
                $update = array(
                    'tab_name' => $tab,
                    'users_list' => implode(",", $currentUsersList),
                    'updated_at' => $curDateTime
                );
                TabUsers::updateOrCreate(
                        ['tab_name' => $tab]
                        , $update);
            }
        }
        return response(json_encode(array('message' => 'crone run successful')), 200)->header('Content-Type', 'application/json');
    }

    public function deleteDetails($id) {
        $data = Users::find($id);
        if (!$data)
            return response(json_encode(array('error' => 'invalid id')), 403)->header('Content-Type', 'application/json');

        $curDateTime = date('Y-m-d H:i:s');
        $delete = array('deleted_at' => $curDateTime);

        Users::updateData($delete, $id);
        return response(json_encode(array('message' => 'details deleted successfully')), 200)->header('Content-Type', 'application/json');
    }

    # logout api

    public function logOut(Request $request) {
        $token = $request->all('token');
        $token = $token['token'];
        $data = StoreTokens::whereToken($token)->delete();

        if ($data)
            return response(json_encode(array('message' => 'Logout successfully')), 200)->header('Content-Type', 'application/json');
    }
    
    public function getKey() {
         return view('profile', array(
                'user' => Auth::user()
            ));
    }
}
