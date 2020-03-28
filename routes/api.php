<?php

use App\Http\Controllers\EventController;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//登入的使用者------------------------
Route::group([
    'prefix' => 'auth'
], function ($router) {
    Route::post('login', 'AuthController@login');
    Route::post('signup', 'AuthController@signup');
    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::post('me', 'AuthController@me');
    Route::post('myAccount','MemberController@myAccount');
    Route::post('updateAccount','MemberController@updateAccount');
    Route::post('uploadImage','AuthController@uploadImage');
});


//管理員------------------------
Route::group(['middleware' => ['JWT','admin']], function () {

    //MemberController
    Route::get('get-members','MemberController@getMembers');
    Route::get('search-member','MemberController@searchMember');
    Route::post('changePayStatus','MemberController@changePayStatus');
    Route::post('executeExpired','MemberController@executeExpired');
    Route::get('getPayHistory/{id}','MemberController@getPayHistory');
    Route::get('getMemberDetail/{id}','MemberController@getMemberDetail');
    Route::post('toValid','MemberController@toValid')->middleware('role:accountant');
    Route::post('addGroupMember','MemberController@addGroupMember');
    Route::get('getUserLevel/{user_id}','MemberController@getUserLevel');
    Route::post('updateMemberAccount','MemberController@updateMemberAccount');
    Route::post('updateMemberPassword/{id_code}','MemberController@updateMemberPassword');
    Route::post('makeGroupLeader','MemberController@makeGroupLeader');
    Route::post('makeTeacher','MemberController@makeTeacher');
});

//Guest 使用者------------------------

    //MemberController
    Route::get('inviterCheck','MemberController@inviterCheck');
    Route::post('extendMemberShip','MemberController@extendMemberShip');
    Route::post('member/join','MemberController@store');
    Route::post('updateMemberLevel','MemberController@updateMemberLevel');//should be auth , admin later

    //活動類別
    Route::apiresource('category','CategoryController');

    //活動
    Route::apiresource('event','EventController');
    Route::get('getEvents','EventController@getEvents');
    Route::post('joinevent/{slug}','EventController@JoinEvent');//should be auth later
    Route::post('cancelevent/{slug}','EventController@CancelEVent');//should be auth later
    Route::post('myevent','EventController@MyEvent');//should be auth later
    Route::get('eventguests/{slug}','EventController@EventGuests');
    Route::get('district','EventController@GetDistrict');
    Route::get('which_category_event/{name}','EventController@which_category_event');
    Route::post('drawEventReward/{slug}','EventController@drawEventReward');//should be auth later
    Route::get('isUserArrive/{slug}','EventController@isUserArrive');
    Route::post('arriveEvent/{slug}','EventController@arriveEvent');
    Route::post('updateEventCurrentDay/{slug}','EventController@updateEventCurrentDay')->middleware('BCP');
    Route::post('updateEventPublicStatus','EventController@updateEventPublicStatus')->middleware('BCP');
    
    //交易
    Route::post('transaction','TransactionController@transaction');
    Route::get('trans-history/{id}','TransactionController@show');

    //折扣兌換卷
    Route::post('couponcode/exchange','PromocodeController@exchange');

    //產品
    Route::apiresource('product','ProductController');
    Route::get('getLocationAndQuantity/{slug}','ProductController@getLocationAndQuantity');
    Route::get('product-category','ProductController@productCategory');
    Route::post('purchase/{Product}','ProductController@purchase');
    Route::get('getAllProduct','ProductController@getAllProduct');

    //經銷據點
    Route::apiresource('location','LocationController');
    Route::get('/order-list/location/{location_id}/{product_id}','LocationController@orderList');

    //訂單
    Route::get('/my-order-list','OrderDetailController@myOrderList');
    

    // Route::get('git-pull',function(){
    //     $output = shell_exec('. /opt/scripts/gitpull');
    //     return response($output);
    // });
    