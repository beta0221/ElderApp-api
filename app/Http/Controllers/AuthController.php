<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Role;
use Illuminate\Support\Facades\Cookie;
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
        $this->middleware('JWT', ['except' => ['login','signup','web_login','view_login']]);
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

        $hasRole = (request('hasRole'))?true:false;

        return $this->respondWithToken($token,$hasRole);
    }

    public function web_login(){
        $credentials = request(['email', 'password']);
        if (! $token = auth('web')->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        Cookie::queue('token',$token,60);
        return redirect('view_me');
    }
    public function view_login(){
        return view('login.login');
    }
    public function view_me(){
        $user = User::web_user();
        return response()->json($user);
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
            'id_code'=>$user->id_code,
            'email'=>$user->email,
            'name'=>$user->name,
            'wallet'=>$user->wallet,
            'rank'=>$user->rank,
            'org_rank'=>$user->org_rank,
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
    protected function respondWithToken($token,$hasRole)
    {

        $user = auth()->user();
        $result = [
            'access_token' => $token,
            'user_id'=>$user->id,
            'id_code'=>$user->id_code,
            'email'=>$user->email,
            'name'=>$user->name,
            'wallet'=>$user->wallet,
            'rank'=>$user->rank,
            'img'=>$user->img,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
        ];

        if($hasRole){
            $roleName = null;
            if($role = $user->roles()->first()){
                $roleName = $role->name;
            }
            $result['role'] = $roleName;
        }

        return response()->json($result);

    }

    public function uploadImage(Request $request)
    {
        
        //handle image
        $image = $request->image;  // your base64 encoded
        $image = str_replace('data:image/png;base64,', '', $image);
        $image = str_replace(' ', '+', $image);
        $imageName = time().'.'.'png';
        $path = 'images/users/'.$request->id;


        
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
