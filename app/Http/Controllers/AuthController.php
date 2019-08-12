<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Role;
use Illuminate\Support\Facades\Storage;

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
        // return response('hello');
    }



    // Sign up part
    public function signup(Request $request)
    {
        
        $user = User::create($request->all());
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
        
        $user = auth()->user();

        return response()->json([
            'user_id'=>$user->id,
            'email'=>$user->email,
            'name'=>$user->name,
            'wallet'=>$user->wallet,
            'rank'=>$user->rank,
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

        
        $user = auth()->user();

        return response()->json([
            'access_token' => $token,
            'user_id'=>$user->id,
            'email'=>$user->email,
            'name'=>$user->name,
            'wallet'=>$user->wallet,
            'rank'=>$user->rank,
            'img'=>$user->img,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
        ]);

    }

    public function uploadImage(Request $request)
    {
        
        //handle image
        $image = $request->image;  // your base64 encoded
        $image = str_replace('data:image/png;base64,', '', $image);
        $image = str_replace(' ', '+', $image);
        $imageName = time().'.'.'png';
        $path = 'images/users/'.$request->name;


        
        if(!Storage::exists($path)){
            Storage::makeDirectory($path);   
        }else{
            \File::cleanDirectory($path);
        }

        \File::put($path.'/'.$imageName, base64_decode($image));
        
        $user = User::find($request->id);
        $user->img = $imageName;
        $user->save();


        return response()->json([
            'status'=>'ok',
            'image_name'=>$imageName,
        ]); 
    }

}
