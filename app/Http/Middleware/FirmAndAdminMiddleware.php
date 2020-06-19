<?php

namespace App\Http\Middleware;

use Closure;

class FirmAndAdminMiddleware
{
    private $acceptGroup = ['admin','employee','accountant','firm'];
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
            if(in_array($role->name,$this->acceptGroup)){
                $isAdmin = true;
            }
        }

        if(!$isAdmin){
            return response('權限不足',400);
        }

        return $next($request);
    }
}
