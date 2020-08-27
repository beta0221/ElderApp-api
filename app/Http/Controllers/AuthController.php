<?php

namespace App\Http\Controllers;

use App\Helpers\ImageResizer;
use Illuminate\Http\Request;
use App\User;
use App\Role;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{

    const iOSVer_requirement = 10102;
    const androidVer_requirement = 2;
    const iOS_store_url = 'https://apps.apple.com/tw/app/%E9%8A%80%E9%AB%AE%E5%AD%B8%E9%99%A2/id1485979712';
    const android_store_url = 'https://play.google.com/store/apps/details?id=com.elderApp.ElderApp';

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('JWT', ['except' => [
            'login',
            'signup',
            'web_login',
            'web_admin_login',
            'view_login',
            'view_admin_login'
        ]]);
    }
    

    private function pleaseUpdateResponse(){
        return response([
            'ios_update_url'=>static::iOS_store_url,
            'android_update_url'=>static::android_store_url,
        ]);
    }
    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        if($iOSVer = request('iOSVer')){
            if(static::iOSVer_requirement > (int)$iOSVer){
                return $this->pleaseUpdateResponse();
            }
        }
        if($androidVer = request('androidVer')){
            if(static::androidVer_requirement > (int)$androidVer){
                return $this->pleaseUpdateResponse();
            }
        }

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
            return view('login.login',[
                'email'=>request('email'),
                'password'=>request('password'),
                'from'=>request('from'),
                'error'=>'帳號或密碼錯誤',
            ]);
        }
        Cookie::queue('token',$token,60);
        
        $from = request('from');
        if(isset($from)){
            return redirect($from);
        }
        return redirect('product/list');
    }
    public function web_admin_login(){
        $credentials = request(['email', 'password']);
        if (! $token = auth('web')->attempt($credentials)) {
            return view('login.admin_login',[
                'email'=>request('email'),
                'password'=>request('password'),
                'from'=>request('from'),
                'error'=>'帳號或密碼錯誤',
            ]);
        }
        Cookie::queue('token',$token,60);
        $from = request('from');
        if(isset($from)){
            return redirect($from);
        }
        return redirect('/');
    }
    public function view_login(){
        $from = request('from');
        return view('login.login',[
            'from'=>$from
        ]);
    }
    public function view_admin_login(){
        $from = request('from');
        return view('login.admin_login',[
            'from'=>$from
        ]);
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
        $user = auth()->user();
        $user->remove_pushtoken();
        
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
            'org_rank'=>$user->org_rank,
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
        
        $user = auth()->user();
        //handle image
        $image = $request->image;  // your base64 encoded
        $image = str_replace('data:image/png;base64,', '', $image);
        $image = str_replace(' ', '+', $image);
        //$image = base64_decode($image);

        $imageName = time().'.'.'png';
        $ftpPath = "/users/$user->id_code/";

        if(Storage::disk('ftp')->exists($ftpPath)){
            $result = Storage::disk('ftp')->deleteDirectory($ftpPath);
            if(!$result){
                return response('error',500);
            }
        }

        $image = ImageResizer::aspectFit($image,80)->encode();
        if(!Storage::disk('ftp')->put($ftpPath . $imageName,$image)){
            return response('error',500);
        }        
        
        $user->img = $imageName;
        $user->save();

        $imgUrl = config('app.static_host') . "/users/$user->id_code/$imageName";
        return response()->json([
            'status'=>'success',
            'imgUrl'=>$imgUrl,
        ]); 
    }

    /**
     * 設定使用者 pushtoken
     */
    public function set_pushtoken(Request $request){
        $this->validate($request,[
            'pushtoken' =>'required',
        ]);
        $user = auth()->user();
        $user->set_pushtoken($request->pushtoken);
        return response('success');
    }

}
