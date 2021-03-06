<?php

namespace App\Http\Middleware;

use Closure;

class ValidMember
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
        $user = $request->user();
        if($user->valid != 1){
            return response('您目前為無效會員。',403);
        }
        return $next($request);
    }
}
