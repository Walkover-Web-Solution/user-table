<?php

namespace App\Http\Controllers;

use App\StoreTokens;
use App\Tabs;
use App\team_table_mapping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\TableStructure;

class UserController extends Controller {

    public function getDetailsOfUserById($tableId, $id) {
        $tableNames = team_table_mapping::getUserTablesNameById($tableId);
        $tableNameArr = json_decode(json_encode($tableNames), true);
        
        if (empty($tableNameArr[0]['table_id'])) {
            echo "no table found";
            exit();
        } else {
            $data = \DB::table($tableNameArr[0]['table_id'])->selectRaw('*')->where('id', $id)->first();
        }
        
        $colDetails = TableStructure::formatTableStructureData($tableNameArr[0]['table_structure']);

        return response(
                        json_encode(
                                array('data' => $data, 'colDetails' => $colDetails, 'authKey' => $tableNameArr[0]['auth'])
                        ), 200
                )->header('Content-Type', 'application/json');
    }

    public function saveFilter(Request $request) {
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
        if (empty($tableId)) {
            return response(json_encode(array('error' => "something went wrong.")), 403)->header('Content-Type', 'application/json');
        } else {
            $tableNames = team_table_mapping::getUserTablesNameById($tableId);
            $tableNameArr = json_decode(json_encode($tableNames), true);
            if (empty($tableNameArr[0]['table_id'])) {
                return response(json_encode(array('error' => "something went wrong.")), 403)->header('Content-Type', 'application/json');
            } else {
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
        {
            return response(json_encode(array('message' => $tab . ' saved successfully')), 200)->header('Content-Type', 'application/json');
        }
        else{
            return response(json_encode(array('error' => "something went wrong.")), 403)->header('Content-Type', 'application/json');
        }
    }

    # logout api

    public function logOut(Request $request) {
        $tokenArr = $request->all('token');
        $token = $tokenArr['token'];
        $data = StoreTokens::whereToken($token)->delete();

        if ($data)
        {
            return response(json_encode(array('message' => 'Logout successfully')), 200)->header('Content-Type', 'application/json');
        }
    }

    public function getKey() {
        return view('profile', array(
            'user' => Auth::user()
        ));
    }

}
