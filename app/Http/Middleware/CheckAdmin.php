<?php

namespace App\Http\Middleware;

use Closure;

class CheckAdmin
{

    private $adminGroup = ['admin','employee','accountant'];
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        
        $roles = $request->user()->roles()->get();
        $isAdmin = false;
        foreach ($roles as $role) {
            if(in_array($role->name,$this->adminGroup)){
                $isAdmin = true;
            }
        }

        if(!$isAdmin){
            return response('admin only',400);
        }

        return $next($request);
    }
}
