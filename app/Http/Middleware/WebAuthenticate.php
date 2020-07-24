<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;

class WebAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next,$user='user')
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (\Throwable $th) {
            $from = $request->path();
            if($user == 'user'){
                return redirect()->route('web_login_page',['from'=>$from]);
            }else if($user == 'admin'){
                return redirect()->route('web_admin_login_page',['from'=>$from]);
            }
        }
        
        return $next($request);
    }
}
