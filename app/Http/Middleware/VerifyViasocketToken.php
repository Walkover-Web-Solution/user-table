<?php

namespace App\Http\Middleware;

use Closure;

class VerifyViasocketToken {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        if (empty($request->header('Authorization'))) {
            return response()->json(array('error' => 'You need proper authorization.'), 401);
        } else {
            $authToken = $request->header('Authorization');
            try {
                $response = \App\Viasocket::getUserTeam($authToken);
                $teams = json_decode($response, true);
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
