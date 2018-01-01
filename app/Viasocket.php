<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use GuzzleHttp;

class Viasocket extends Model
{
    public static function getUserProfile($authToken){
        $client = new GuzzleHttp\Client();
        $request = $client->get(env('SOCKET_API_URL') . '/users/profile.json', ['headers' => ['Authorization' => $authToken]]);
        return $request->getBody()->getContents();
    }
    
    public static function getUserTeam($authToken){
        $client = new GuzzleHttp\Client();
        $request = $client->get(env('SOCKET_API_URL') . '/teams.json', ['headers' => ['Authorization' => $authToken]]);
        return $request->getBody()->getContents();
    }

    public static function sendUserToAPI($email){
        $client = new GuzzleHttp\Client();
        $request = $client->get("https://sokt.io/vsnxPNqGEsNdN3umwTtF/usertable-new-user?email=$email" , ['headers' => []]);
        return $request->getBody()->getContents();
    }

    public static function getTeamArray($response){
        $team_response_arr = json_decode($response, true);
        $team_response = $team_response_arr['teams'];
        $team_array = array();
        foreach ($team_response as $value) {
            $team_array[$value['id']] = $value['name'];
        }
        return $team_array;
    }
    public static function getTeamIdArray($response){
        $team_response_arr = json_decode($response, true);
        $team_response = $team_response_arr['teams'];
        $team_array = array();
        foreach ($team_response as $value) {
            $team_array[] = $value['id'];
        }
        return $team_array;
    }
}
