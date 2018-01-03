<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use GuzzleHttp;
use Illuminate\Support\Facades\Auth;

class Teams extends Model
{

    public static function getTeamMembers($teamId) {
        try {
            $authToken = session()->get('socket_token');
            $client = new GuzzleHttp\Client();
            $request = $client->get(env('SOCKET_API_URL') . '/teams/' . $teamId . '/memberships.json', ['headers' => ['Authorization' => $authToken]]);
            $response = $request->getBody()->getContents();
            $team_response_arr = json_decode($response, true);

            if($loggedInUser = Auth::user())
                $email = $loggedInUser->email;
            else
                $email = 'me';
            $member_array = array(0 => array('email' => '', 'name' => 'No One'),1=>array('email' => 'me', 'name' => 'Me'));
            foreach ($team_response_arr['memberships'] as $member) {
                $email = $member['user']['email'];
                if (empty($member['user']['first_name']) && empty($member['user']['last_name'])) {
                    $name = $email;
                } else {
                    $name = $member['user']['first_name'] . " " . $member['user']['last_name'];
                }
                $member_array[] = array('email' => $email, 'name' => $name);
            }
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $member_array = array(0 => array('email' => '', 'name' => 'No One'));
        }
        return $member_array;
    }
    
}