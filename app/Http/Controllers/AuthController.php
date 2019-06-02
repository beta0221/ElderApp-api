<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Role;
use App\UserDetial;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('JWT', ['except' => ['login','signup']]);
    }
    
    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);

        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }



    // Sign up part
    public function signup(Request $request)
    {
        
        $user = User::create($request->all());
        UserDetial::create([
            'id'=>$user->id,
        ]);

        $user->roles()->attach(Role::where('name','user')->first());

        return $this->login($request);
    }


    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        $detial = auth()->user()->detial;
        $user = auth()->user();

        return response()->json([
            'user_id'=>$user->id,
            'email'=>$user->email,
            'name'=>$user->name,
            'wallet'=>$detial->wallet,
            'rank'=>$detial->rank,
        ]);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {

        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {

        $detial = auth()->user()->detial;
        $user = auth()->user();

        return response()->json([
            'access_token' => $token,
            'user_id'=>$user->id,
            'email'=>$user->email,
            'name'=>$user->name,
            'wallet'=>$detial->wallet,
            'rank'=>$detial->rank,
            'img'=>$detial->img,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
        ]);

    }
}
