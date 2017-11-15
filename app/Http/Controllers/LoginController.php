<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp;

class LoginController extends Controller {

    public function login(Request $request) {

        $authToken = $request->input('sokt-auth-token');
        if (empty($authToken)) {
            //redirect to unsuccessful login page 
            return redirect()->route('unauthorised');
        }
        
        $authToken = 'Bearer ' . $authToken;
        //$authToken = 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJkYXRhIjp7ImVtYWlsIjoiaGF0aW1Ad2Fsa292ZXIuaW4ifSwiaXNzIjoidmlhc29ja2V0LmNvbSIsImV4cCI6MTUxMDY0NzE2MSwiaWF0IjoxNTEwNTYwNzYxfQ.0RLFFAqj79v4ZSfEvr0X4Ro0c7A4IDAzCEMk1WeWdxY';
        session()->put('authtoken', $authToken);
        $this->getUserTeam($authToken);
        return redirect()->route('tables');
        
    }

    public function getUserTeam($authToken) {

        $client = new GuzzleHttp\Client();
        $request = $client->get('https://api.viasocket.com/teams.json',['headers' => ['Authorization' => $authToken]]);
        
        $response = $request->getBody()->getContents();
        $team_response  = json_decode($response,true);
        $team_response = $team_response['teams'];
        $team_array = array();
        
        foreach ($team_response  as $key =>$value){
            $team_array[$value['id']] = $value['name'];
        }
        session()->put('team_array', $team_array);
        return TRUE;
    }

}
