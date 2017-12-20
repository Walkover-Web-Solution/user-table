<?php

namespace App\Http\Middleware;

use App\team_table_mapping;
use Closure;
use GuzzleHttp\Exception\RequestException;

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
                $response = team_table_mapping::getTableByAuth($authToken);
                if(empty($response)){
                    return response()->json(array('error' => 'You need proper authorization.'), 401);
                }
            } catch (RequestException $e) {
                return response()->json(json_decode($e->getResponse()->getBody()->getContents()), 401);
            }
        }
        return $next($request);
    }
}
