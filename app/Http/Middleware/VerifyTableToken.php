<?php

namespace App\Http\Middleware;

use Closure;

class VerifyTableToken {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        if (empty($request->header('Auth-Key'))) {
            return response()->json(array('error' => 'You need proper authorization.'), 401);
        } else {
            $authToken = $request->header('Auth-Key');
            try {
                $response = \App\team_table_mapping::getTableByAuth($authToken);
                $teams = json_decode($response, true);
                print_r($teams);die;
                if(empty($teams)){
                    return response()->json(array('error' => 'You need proper authorization.'), 401);
                }
            } catch (\GuzzleHttp\Exception\RequestException $e) {
                return response()->json(json_decode($e->getResponse()->getBody()->getContents()), 401);
            }
        }
        return $next($request);
    }
}
