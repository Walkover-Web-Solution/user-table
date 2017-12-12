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

        if (empty($tableNames['table_id'])) {
            echo "no table found";
            exit();
        } else {
            $data = \DB::table($tableNames['table_id'])->selectRaw('*')->where('id', $id)->first();
        }

        $colDetails = TableStructure::formatTableStructureData($tableNames['table_structure']);

        foreach($tableNames['table_structure'] as $k=>$v)
        {
            $newarr[$v['column_name']] = $v;
        }

        foreach($newarr as $k=>$v)
        {
            $orderNeed[] = $k;
        }

        $data = json_decode(json_encode($data),true);
        $newData = $this->orderArray($data, $orderNeed);

        return response(
                        json_encode(
                                array('data' => $newData, 'colDetails' => $colDetails,
                                    'authKey' => $tableNames['auth'])
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
            if (empty($tableNames['table_id'])) {
                return response(json_encode(array('error' => "something went wrong.")),
                        403)->header('Content-Type', 'application/json');
            } else {
                $tableId = $tableNames['table_id'];
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

    function orderArray($arrayToOrder, $keys)
    {
        foreach($keys as $key)
        {
            $inner_ordered[$key] = $arrayToOrder[$key];
        }
        $inner_ordered['id'] = $arrayToOrder['id'];
        return json_decode(json_encode($inner_ordered));
    }

}
