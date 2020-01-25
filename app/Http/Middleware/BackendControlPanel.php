<?php

namespace App\Http\Middleware;

use Closure;

class BackendControlPanel
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
        if($request->user()->roles()->count() == 0){
            return response('admin only',400);
        }
        return $next($request);
    }
}
