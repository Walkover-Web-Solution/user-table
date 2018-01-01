<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;
use Illuminate\Support\Facades\Hash;
use App\Viasocket;

class LoginController extends Controller {

    public function login(Request $request) {

        $authToken = $request->input('sokt-auth-token');
        if (empty($authToken)) {
            //redirect to unsuccessful login page 
            return redirect()->route('unauthorised');
        }
        $authToken = 'Bearer ' . $authToken;
        session()->put('socket_token', $authToken);
        $userdata = $this->getUserDetail($authToken);
        if ($userdata) {
            $user = User::where(['email' => $userdata['email']])->first();
            if (!$user) {
                $user = new User();
                $user->email = $userdata['email'];
                $user->first_name = $userdata['first_name'];
                $user->last_name = $userdata['last_name'];
                $user->company = $userdata['company'];
                $user->identifier = $userdata['identifier'];
                $user->api_token = str_random(60); //slight change here
                $password = str_random(8);
                $user->password = Hash::make($password); //md5($password);
                $user->save();
                //Call the API to send email
                $r = Viasocket::sendUserToAPI($userdata['email']);
            }
            $this->getUserTeam($authToken);
            // attempt to do the login
            if ($user) {
                Auth::login($user);
                return redirect()->route('tables');
            } else {
                return redirect()->route('unauthorised');
            }
        }else{
            return redirect()->route('unauthorised');
        }
    }

    public function getUserDetail($authToken) {
        try {
            $response = Viasocket::getUserProfile($authToken);
            return $user = json_decode($response, true);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            return false;
        }
    }

    public function getUserTeam($authToken) {
        try {
            $response = Viasocket::getUserTeam($authToken);
            $team_response_arr = json_decode($response, true);
            $team_response = $team_response_arr['teams'];
            $team_array = array();
            foreach ($team_response as $value) {
                $team_array[$value['id']] = $value['name'];
            }
            session()->put('team_array', $team_array);
            return TRUE;
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            return FALSE;
        }
    }

}
