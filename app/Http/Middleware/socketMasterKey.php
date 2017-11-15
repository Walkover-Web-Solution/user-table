<?php

namespace App\Http\Middleware;

use Closure;

class socketMasterKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->header('Authorization') != 'QIgd3CpnvadlzLp6dsV4') {
            return response()->json(array('error' => 'You need proper authorization.'));
        }
        return $next($request);
    }
}
