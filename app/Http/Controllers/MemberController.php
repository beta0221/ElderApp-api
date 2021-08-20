<?php

namespace App\Http\Controllers;

use App\Association;
use App\Exports\GroupMembers;
use App\Helpers\Pagination;
use App\Helpers\Tracker;
use Illuminate\Http\Request;
use App\User;
use App\PayDate;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cookie;
use Maatwebsite\Excel\Facades\Excel;

class MemberController extends Controller
{

    public function __construct()
    {
        $this->middleware('JWT', ['except' => [
            'create',
            'welcome',
            'inviterCheck',
            'cacu',
            'store',
            'memberTree',
            'moveMemberPage',
            'sendGroupRewardPage',
            'sendGroupReward',
            'moveMember',
            'promoteLeader',
            'updateMemberLevel',
            'memberGroupMembers_list',
            'memberGroupMembers',
            'getAllAssociation',
        ]]);
        $this->middleware('webAuth:admin', ['only' => ['memberGroupMembers','memberGroupMembers_list']]);
    }

    //管理後台的搜尋會員請求
    public function searchMember(Request $request)
    {
        $searchColumn = ($request->searchColumn)?$request->searchColumn:'id_number';
        
        $query = null;
        if($request->searchColumn == 'name'){
            $query = User::where('name','like',"%$request->searchText%");
        }else{
            $query = User::where($searchColumn,$request->searchText);
        }

        $request_user = request()->user();
        if(!$request_user->isAdmin()){
            $query->where('association_id',$request_user->association_id);
        }

        $user = $query->get();

        return response()->json($user);
    }


    //管理後台使用的請求
    public function getMembers(Request $request)
    {

        $page = $request->page;
        $rows = $request->rowsPerPage;
        $skip = ($page - 1) * $rows;
        $ascOrdesc = 'desc';
        if ($request->descending == null || $request->descending == 'false') {
            $ascOrdesc = 'asc';
        }
        $orderBy = ($request->sortBy) ? $request->sortBy : 'id';
        $column = ($request->column) ? $request->column : null;
        $value = (isset($request->value)) ? $request->value : null;
        $blurSearch = ($request->blurSearch) ? true : false;

        $query = DB::table('users')
            ->select('id','name','org_rank','email','tel','phone','gender','rank','inviter','inviter_phone','pay_status','created_at','expiry_date','valid','birthdate','id_number','id_code')
            ->orderBy($orderBy, $ascOrdesc);

        $user = request()->user();
        if(!$user->isAdmin()){
            $query->where('association_id',$user->association_id);
        }

        if($column != null && $value != null){
            if(($column == 'id' || $column == 'name') && strpos($value,',') == true){
                $idArray = explode(',',$value);
                $query->whereIn($column,$idArray);
            }else{
                if($blurSearch){
                    $query->where($column,'like','%'.$value.'%');
                }else{
                    $query->where($column,$value);
                }
            }
        }    
        $total = $query->count();
        $users = $query->skip($skip)->take($rows)->get();
        
        $queryResult = null;
        if($column == 'name' && strpos($value,',') == true){
            $nameArray = explode(',',$value);
            $queryResult = $this->handleQueryResult($nameArray,$users);
        }

        return response()->json([
            'users' => $users,
            'total' => $total,
            'queryResult' => $queryResult,
        ]);
    }

    /**整理查無名單＆重複名單 */
    private function handleQueryResult($nameArray,$users){
        $queryResult = null;
        $nameNotFound = [];
        
        foreach ($nameArray as $name) {
            $found = false;
            foreach ($users as $user) {
                if($user->name == $name){$found = true;}
            }
            if(!$found){
                $nameNotFound[] = $name;
            }
        }
        
        $nameCount = [];
        foreach ($users as $user) {
            if(isset($nameCount[$user->name])){
                $nameCount[$user->name] += 1;
            }else{
                $nameCount[$user->name] = 1;
            }
        }
        
        $nameRepeat = [];
        foreach ($nameCount as $name => $count) {
            if($count > 1){
                $nameRepeat[] = $name;
            }
        }

        if(!empty($nameNotFound) || !empty($nameRepeat)){
            $queryResult = [
                'nameNotFound'=>$nameNotFound,
                'nameRepeat'=>$nameRepeat,
            ];
        }

        return $queryResult;
    }

    public function create()
    {
        $associationList = Association::all();
        return view('member.join',[
            'associationList'=>$associationList,
        ]);
    }


    //註冊新會員
    public function store(Request $request)
    {
        $request->validate([
            // 'association_id' => 'required',
            'email' => 'required|unique:users|min:8',
            'password' => 'required',
            'name' => 'required',
            //'gender' => 'required',//
            //'birthdate'=>'required',//
            //'id_number'=>'required',//
            'district_id' => 'required',
            //'address' => 'required',//
            'pay_method'=>'required',
        ]);
        
        if(!$request->has('association_id')){
            $request->merge(['association_id'=>1]);
        }

        if($request->pay_method == 1){
            $inviter = User::where('phone',$request->inviter_id_code)->orWhere('id_code',$request->inviter_id_code)->firstOrFail();
            $request->merge([
                'inviter_id'=>$inviter->id,
                'inviter'=>$inviter->name,
                'inviter_phone'=>$inviter->phone,
            ]);    
        }
        
        $id_code = $this->generateIdCode();
        $request->merge([
            'pay_status'=> 1,
            'id_code'=> $id_code,
        ]);
        
        try {
            $user = User::create($request->all());
        } catch (\Throwable $th) {
            return response($th);
        }

        if($request->pay_method == 1){
            try {
                $row = DB::table('user_group')->where('user_id',$inviter->id)->first();
                if($row){
                    DB::table('user_group')->insert([
                        'group_id'=>$row->group_id,
                        'user_id'=>$user->id,
                        'level'=>1,
                        'lv_1'=>$user->id,
                        'lv_2'=>$row->lv_2,
                        'lv_3'=>$row->lv_3,
                        'lv_4'=>$row->lv_4,
                        'lv_5'=>$row->lv_5,
                    ]);
                }
            } catch (\Throwable $th) {
                return response($th);
            }
        }


        if($request->app){
            return response()->json([
                's'=>1,
                'user'=>$request,
            ]);
        }else{
            return view('member.welcome',['id_code'=>$id_code]);
        }

        
    }


    private function generateIdCode(){
        $lastId = User::select('id')->orderBy('id','desc')->first()->id;
        if(!$lastId){
            $lastId = 0;
        }
        $nextId = $lastId + 1;
        while (strlen($nextId) < 4) {
            $nextId = '0'.$nextId;
        }
        $id_code = 'H' . substr(date('Y'),-2) . date('m') . $nextId . rand(0,9);
        return $id_code;
    }

    //更新會員付款狀態
    public function changePayStatus(Request $request){
        Tracker::log($request);

        $isAccountant = $request->user()->hasRole('accountant');
                
        if(is_array($request->id)){
            $users = User::whereIn('id',$request->id)->get();
            foreach ($users as $user) {
                $this->handlePayStatus($user,$isAccountant);
            }
        }else{
            $user = User::where('id',$request->id)->firstOrFail();
            $this->handlePayStatus($user,$isAccountant);
        }

        return response('success');
    }

    /**處理會員下階段狀態 */
    private function handlePayStatus(User $user,$isAccountant){
        
        // if(!$user->isSameAssociation()){
        //     return response('錯誤操作', 403);
        // }
        
        $p = $user->pay_status;
        if ($p < 2) {
            $p++;
            $user->update(['pay_status'=>$p]);
        }else if($p == 2){
            if(!$isAccountant){ return; }

            if(is_null($user->expiry_date)){    //新入會
                $user->welcome();
            }else{  //續會
                $user->renew();
            }
        }
    }

    //取得會員付款歷史紀錄
    public function getPayHistory($id){
        $user = User::findOrFail($id);
        return response()->json($user->payHistory()->get());
    }

    //取得會員詳細基本資料
    public function getMemberDetail($id){
        $user = User::findOrFail($id);
        return response()->json($user);
    }


    /**
     *更新會員資格（無效會員變成有效會員） 
     */
    public function toValid(Request $request){
        Tracker::log($request);
        $user = User::where('id',$request->id)->firstOrFail();
        
        if(!$user->isSameAssociation()){
            return response('錯誤操作', 403);
        }

        if(!$user->expiry_date){
            return response('使用者尚未入會，因此無法進行續會',Response::HTTP_BAD_REQUEST);
        }

        $user->renew();

        return response('success');

    }

    /**
     *檢查推薦人是否存在 
     */
    public function inviterCheck(Request $request){
        
        $request->validate([
            'inviter_id_code'=>'required',
        ]);
        $inviter = User::where('id_code',$request->inviter_id_code)->first();
        
        if(!$inviter){
            $inviter = User::where('phone',$request->inviter_id_code)->first();
        }

        if(!$inviter){
            return response()->json([
                's'=>0,
            ]);
        }else{
            return response()->json([
                's'=>1,
                'inviter'=>$inviter->name,
            ]);
        }
    }


    /**
     *大天使小天使加入組織請求 
     */
    public function addGroupMember(Request $request){
        Tracker::log($request);
        // leader_id
        //user_id
        //level

        $leader = User::find($request->leader_id);
        $addUser = User::find($request->user_id);

        if($leader->association_id != $addUser->association_id){
            return response('錯誤操作', 403);
        }
        
        if(!$addUser || !$leader){
            return response()->json([
                's'=>0,'m'=>'會員帳號不存在',
            ]);
        }

        if($request->leader_id == $request->user_id){
            return response()->json([
                's'=>0,'m'=>'無效的操作',
            ]);
        }

        $leader_level = $leader->groupLevel();
        if($leader_level <= 0){
            return response()->json([
                's'=>0,'m'=>'所選的使用者無所屬組織。',
            ]);
        }

        if($request->level >= $leader_level){
            return response()->json([
                's'=>0,'m'=>'所選等級不能高於使用者等級',
            ]);
        }

        if($addUser->groupLevel() <= 0){
            if(!$result = $addUser->joinToGroup($leader->id,$request->level)){
                return response()->json([
                    's'=>0,'m'=>'系統錯誤',
                ]);
            }
        }else if($addUser->isPrimaryLeaderOfGroup()){

            if($addUser->groupLevel() != $request->level){
                return response()->json([
                    's'=>0,'m'=>'遷移組織只能指派為目前職位',
                ]);
            }

            if(!$result = $addUser->mergeToGroup($leader->id)){
                return response()->json([
                    's'=>0,'m'=>'系統錯誤',
                ]);
            }
        }else{
            return response()->json([
                's'=>0,'m'=>'此會員已經存在所屬組織。',
            ]);
        }

        return response()->json([
            's'=>1,'m'=>'新增成功',
        ]);

    }

    /**
     *把使用者從組織中移除 
     */
    public function removeMemberFromGroup(Request $request){
        //user_id
        if(!$user = User::find($request->user_id)){
            Session::flash('error', '查無使用者');
            return redirect()->back();
        }
        if($user->isPrimaryLeaderOfGroup()){
            Session::flash('error', '無法將組織所有者刪除');
            return redirect()->back();
        }

        if(!$result = $user->removeMemberFromGroup()){
            Session::flash('error', '系統錯誤');
            return redirect()->back();
        }
        Session::flash('success', '完成');
        return redirect()->back();

    }

    /**
     * 移動組織成員的操作頁面
     */
    public function moveMemberPage(Request $request,$user_id){
        if(!$user = User::find($user_id)){
            return response('no such user');
        }
        $isLeader = $user->isPrimaryLeaderOfGroup();

        $rows = $user->getGroupUserRows();
        $levelDict = [];
        foreach ($rows as $row) {
            $levelDict[$row->user_id] = $row->level;
        }

        $group_users = $user->getGroupUsers(2);

        return view('member.moveMemberPage',[
            'user'=>$user,
            'group_users'=>$group_users,
            'levelDict'=>json_encode($levelDict),
            'isLeader'=>$isLeader,
            'app'=>$request->has('app'),
        ]);
    }

    /**
     * 移動成員請求
     */
    public function moveMember(Request $request,$user_id){

        $this->validate($request,[
            'target_user_id'=>'required',
            'target_level'=>'required',
        ]);

        if(!$user = User::find($user_id)){
            return response('no such user');
        }

        $redirect = "member_tree/$user->id_code";
        if($request->has('app')){
            $redirect = "memberGroupMembers";
        }

        if($user->isPrimaryLeaderOfGroup()){
            Session::flash('error', '無法將組織所有者移動');
            return redirect($redirect);
        }

        $target = User::find($request->target_user_id);
        if($target->association_id != $user->association_id){
            return response('錯誤操作', 403);
        }

        if(!$result = $user->removeMemberFromGroup()){
            Session::flash('error', '系統錯誤');
            return redirect($redirect);
        }

        if(!$result = $user->joinToGroup($request->target_user_id,$request->target_level)){
            Session::flash('error', '系統錯誤');
            return redirect($redirect);
        }
        
        Session::flash('success', '完成');
        return redirect($redirect);
    }


    /**補發組織獎勵操作頁面 */
    public function sendGroupRewardPage(Request $request,$user_id){
        if(!$user = User::find($user_id)){
            return response('no such user');
        }
        $aboveGroupUsers = $user->getAboveGroupUsers();
        return view('member.sendGroupRewardPage',[
            'user'=>$user,
            'aboveGroupUsers'=>$aboveGroupUsers
        ]);
    }

    /**補發組織獎勵請求 */
    public function sendGroupReward(Request $request,$user_id){
        if(!$user = User::find($user_id)){
            return response('no such user');
        }

        switch ($request->action) {
            case 'renew':
                $user->rewardGroupMembersForRenew();
                break;
            case 'join':
                $user->rewardGroupMembers();
                break;
            default:
                break;
        }

        Session::flash('success', '發送完成');
        return redirect("/member_tree/$user->id_code");
    }

    /**
     * 升級組長等級
     */
    public function promoteLeader(Request $request){
        $this->validate($request,[
            'user_id'=>'required',
            'target_level'=>'required',
        ]);
        if(!$user = User::find($request->user_id)){
            return response('no such user');
        }
        if(!$user->isPrimaryLeaderOfGroup()){
            Session::flash('error', '此使用者不屬於組織所有者');
            return redirect("member_tree/$user->id_code");
        }

        $user_level = $user->groupLevel();
        if($user_level >= $request->target_level){
            Session::flash('error', '目標等級必須為使用者目前等級以上');
            return redirect("member_tree/$user->id_code");
        }

        if(!$result = $user->promoteGroupLeader((int)$request->target_level)){
            Session::flash('error', '系統錯誤');
            return redirect("member_tree/$user->id_code");
        }

        Session::flash('success', '完成');
        return redirect("member_tree/$user->id_code");
    }

    /**
     * get 使用者階級
     */
    public function getUserLevel($user_id){
        if($user = User::find($user_id)){
            return response()->json($user->groupLevel());
        }
        return 0;
    }

    /**
     * 指派使用者為某階級
     */
    public function makeGroupLeader(Request $request){
        Tracker::log($request);
        //user_id
        //level
        if(!$user = User::find($request->user_id)){
            return response()->json([
                's'=>0,'m'=>'查無使用者',
            ]);
        }
        if($user->groupLevel() > 0){
            return response()->json([
                's'=>0,'m'=>'此使用者目前已存在職位。',
            ]);
        }
        if(!$result = $user->makeGroupLeader($request->level)){
            return response()->json([
                's'=>0,'m'=>'系統錯誤',
            ]);
        }
        return response()->json([
            's'=>1,'m'=>'指派成功',
        ]);
    }

    /**
     * 移除使用者為職務
     */
    public function removeGroupLeader(Request $request){
        Tracker::log($request);
        //user_id
        if(!$user = User::find($request->user_id)){
            return response()->json([
                's'=>0,'m'=>'查無使用者',
            ]);
        }
        $user_level = $user->groupLevel();
        if($user_level <= 1){

            if($user->org_rank != null){
                $user->org_rank = null;
                $user->save();
                return response()->json([
                    's'=>1,'m'=>'解除成功',
                ]);
            }

            return response()->json([
                's'=>0,'m'=>'此使用者無所屬職位。',
            ]);
        }
        if(!$result = $user->removeGroupLeader($user_level)){
            return response()->json([
                's'=>0,'m'=>'系統錯誤',
            ]);
        }
        return response()->json([
            's'=>1,'m'=>'解除成功',
        ]);
    }

    /**
     * 指派為老師
     */
    public function makeTeacher(Request $request){
        Tracker::log($request);

        //user_id
        if(!$user = User::find($request->user_id)){
            return response()->json([
                's'=>0,'m'=>'查無使用者',
            ]);
        }

        if(!$user->isSameAssociation()){
            return response('錯誤操作', 403);
        }

        if(!$result = $user->becomeRole('teacher')){
            return response()->json([
                's'=>0,'m'=>'系統錯誤',
            ]);
        }

        return response()->json([
            's'=>1,'m'=>'指派成功',
        ]);
    }

    
    /**
     * App 的 AccountPage OnAppearing 請求
     */
    public function myAccount(){
        $user = auth()->user();
        if($user->img){
            $img = config('app.static_host') . "/users/$user->id_code/$user->img";
            $user->img = $img;
        }

        //據點管理
        if($user->hasRole('location_manager')){
            $user->locationUrl = '/view_myLocation';
        }
        
        //課程管理
        if($user->hasRole('teacher')){
            $user->myCourseUrl = '/view_myCourse';
        }else if($user->manage_events()->first()){
            $user->myCourseUrl = '/view_myCourse';
        }

        //是否綁定Line
        $user->isLineAccountBinded = $user->isLineAccountBinded();

        return response()->json($user);
    }

    /**
     * App首頁更新基本資料
     */
    public function updateAccount(Request $request){
        $user = Auth::user();
        
        if(!$user){
            return response()->json([
                's'=>0,
                'm'=>'身份驗證失敗請重新登入',
            ]);
        }

        $user->name = $request->name;
        $user->phone = $request->phone;
        $user->tel = $request->tel;
        $user->address = $request->address;
        $user->id_number = $request->id_number;
        $user->save();

        return response()->json([
            's'=>1,
            'm'=>'成功更新資料',
        ]);
    }

    /**
     * 更新基本資料請求
     */
    public function updateMemberAccount(Request $request){
        Tracker::log($request);

        $user = User::find($request->id);
        Tracker::info(json_encode($user));

        if(!$user){
            return response()->json([
                's'=>0,
                'm'=>'使用者不存在',
            ]);
        }

        if(!$user->isSameAssociation()){
            return response('錯誤操作', 403);
        }

        if($user->email != $request->email){
            $user->email = $request->email;
            if($existUser = User::where('email',$request->email)->first()){
                if($user->id != $existUser->id){
                    return response()->json(['s'=>0,'m'=>'帳號已存在',]);
                }
            }
        }

        $user->name = $request->name;
        $user->id_number = $request->id_number;
        $user->birthdate = $request->birthdate;
        $user->phone = $request->phone;
        $user->tel = $request->tel;
        $user->address = $request->address;
        $user->gender = (int)$request->gender;
        $user->valid = $request->valid;
        $user->invoice = $request->invoice;
        if($user->pay_status != 3 && $request->pay_status != 3){
            $user->pay_status = $request->pay_status;
        }
        $user->save();

        return response()->json([
            's'=>1,
            'm'=>'成功更新資料',
        ]);
    }

    /**
     * 管理後台 幫會員變更密碼 請求
     */
    public function updateMemberPassword(Request $request,$id_code){
        Tracker::log($request);
        
        if($request->adminCode == 'ji3g4ej03xu3m06'){

            $user = User::where('id_code',$id_code)->firstOrFail();

            if(!$user->isSameAssociation()){
                return response('錯誤操作', 403);
            }

            $user->update([
                'password'=>$request->password
            ]);

            return response('success',200);
        }
        return response('權限不足',400);
        
    }

    /**
     * 會員續會申請
     */
    public function extendMemberShip(Request $request){
        $user = User::find($request->user_id);
        if(!$user){
           return response('error',400);
        }

        if($user->valid != 0){
            return response()->json([
                's'=>0,
                'm'=>'您的會員效期尚未到期。',
            ]);
        }

        if($user->pay_status == 1){
            return response()->json([
                's'=>1,
                'm'=>'我們已收到您的續會申請，將會請工作人員向您聯繫。',
            ]);
        }

        try {
            $user->pay_status = 1;
            $user->save();
            return response()->json([
                's'=>1,
                'm'=>'我們已收到您的續會申請，將會請工作人員向您聯繫。',
            ]);
        } catch (\Throwable $th) {
            return response($th);
        }
    }

    /**
     * 組織樹 頁面
     */
    public function memberTree($id_code){
        $user = User::where('id_code',$id_code)->first();
        if(!$user){
            return response('此使用者會員編號不存在');
        }
        $group_users = $user->getGroupUserRows();
        if(count($group_users)<=0){
            return response('使用者並無所屬組織。');
        }

        $dic=[];
        $validDic=[];
        foreach ($group_users as  $g_user) {
            if($_user = User::select('name','valid')->where('id',$g_user->user_id)->first()){
                $dic[$g_user->user_id] = $_user->name;
                $validDic[$g_user->user_id] = $_user->valid;
            }
        }

        return view('member.tree',[
            'user_id'=>$user->id,
            'group_users'=>json_encode($group_users),
            'name_dic'=>json_encode($dic),
            'valid_dic'=>json_encode($validDic)
        ]);

    }

    public function excel_memberGroupMembers(Request $req){
        $user = User::findOrFail($req->user_id);
        $users = $user->getGroupUsers();
        return Excel::download(new GroupMembers($users),$user->name . '_組織人員表.xlsx');
    }

    private function memberGroupMembers_sublist(User $user){
        $group_users = $user->getSubGroupUserRows();
        $all_user_id_array = [];
        foreach ($group_users as $g_user) {
            $all_user_id_array[] = $g_user->user_id;
        }

        $all_users = User::whereIn('id',$all_user_id_array)->orderBy('org_rank','desc')->get();
        return view('member.memberGroupMembers_list',[
            'users'=>$all_users,
            'showTreeButton'=>false,
        ]);
    }

    public function memberGroupMembers_list(Request $request){
        $user = User::web_user();
        if($user->org_rank < 3){ return '權限不足'; }

        $group_users = $user->getGroupUserRows();
        if(count($group_users)<=0){ return response('使用者並無所屬組織。'); }

        $all_user_id_array = [];
        foreach ($group_users as $g_user) {
            $all_user_id_array[] = $g_user->user_id;
        }

        $all_users = User::whereIn('id',$all_user_id_array)->orderBy('org_rank','desc')->get();

        return view('member.memberGroupMembers_list',[
            'users'=>$all_users,
            'showTreeButton'=>true,
        ]);

    }

    public function memberGroupMembers(Request $request){

        $user = User::web_user();
        if($request->has('token')){ Cookie::queue('token',$request->token); }
        if($user->org_rank < 2){ return '權限不足'; }
        if($user->org_rank == 2){ return $this->memberGroupMembers_sublist($user); }
        
        $group_users = $user->getGroupUserRows();
        if(count($group_users)<=0){ return response('使用者並無所屬組織。'); }

        $all_user_id_array = [];
        foreach ($group_users as $g_user) {
            $all_user_id_array[] = $g_user->user_id;
        }

        $all_users = User::whereIn('id',$all_user_id_array)->get();

        $dic=[];
        $validDic=[];
        $valid_amount = 0;
        $invalid_amount = 0;
        foreach ($all_users as $user) {
            $dic[$user->id] = $user->name;
            $validDic[$user->id] = $user->valid;
            if($user->valid){
                $valid_amount++;
            }else{
                $invalid_amount++;
            }
        }

        return view('member.memberGroupMembers2',[
            'total_amount'=>count($group_users),
            'valid_amount'=>$valid_amount,
            'invalid_amount'=>$invalid_amount,
            'group_users'=>json_encode($group_users),
            'name_dic'=>json_encode($dic),
            'valid_dic'=>json_encode($validDic)
        ]);
    }

    public function updateMemberLevel(Request $request){
        Tracker::log($request);

        $this->validate($request,[
            'user_id'=>'required',
            'level'=>'required|numeric|min:1|max:5'
        ]);

        $row = DB::table('user_group')->where('user_id',$request->user_id)->select('group_id','level')->first();
        if(!$row){
            return response()->json([
                's'=>0,
                'm'=>'使用者不存在'
            ]);
        }

        $old_level = $row->level;
        $old_lv = "lv_" .  $row->level;

        if($old_level == 5){
            return response()->json([
                's'=>0,
                'm'=>'無法更改此用戶等級。'
            ]);
        }

        $lv = "lv_" . $request->level;

        try {
            DB::table('user_group')->where('user_id',$request->user_id)->update([
                'level'=>$request->level,
                $lv => $request->user_id
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                's'=>0,
                'm'=>'系統錯誤',
            ]);
        }

        try {
            DB::table('user_group')->where('group_id',$row->group_id)->where($old_lv,$request->user_id)->update([
                $old_lv=>null
            ]);
        } catch (\Throwable $th) {
            DB::table('user_group')->where('user_id',$request->user_id)->update([
                'level'=>$old_level,
                $lv => null
            ]);
            return response()->json([
                's'=>0,
                'm'=>'系統錯誤.error code : 2781',
            ]);
        }

        return response()->json([
            's'=>1,
            'm'=>'更新成功'
        ]);
    }

    /**變更推薦人 */
    public function updateInviter(Request $request){
        Tracker::log($request);

        if($request->user_id == $request->inviter_id){
            return response('無法將推薦人設立為使用者本身',400);
        }
        $user = User::findOrFail($request->user_id);
        Tracker::info(json_encode($user));

        $inviter = User::findOrFail($request->inviter_id);
        $user->inviter_id = $inviter->id;
        $user->inviter = $inviter->name;
        $user->save();
        return response('success');
    }

    /**
     * 註冊完成 welcome page
     */
    public function welcome(){
        return view('member.welcome');
    }

    public function getAllAssociation(){
        return response(Association::all());
    }

    public function getGroup(Request $request){
        $page = ($request->page)?$request->page:1;
        $groupLeader = DB::table('user_group')->select('group_id')->groupBy('group_id')->get();
        return response()->json($groupLeader);
    }
    
}


