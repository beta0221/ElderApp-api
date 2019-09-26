<?php

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

});

Route::group(['middleware' => ['admin']], function () {
    Route::get('get-members','MemberController@getMembers');
    Route::get('search-member','MemberController@searchMember');
    Route::post('changePayStatus','MemberController@changePayStatus');
    Route::post('executeExpired','MemberController@executeExpired');
    Route::get('getPayHistory/{id}','MemberController@getPayHistory');
    Route::get('getMemberDetail/{id}','MemberController@getMemberDetail');
    Route::post('toValid','MemberController@toValid');
    
    Route::get('getMemberGroupMembers/{id}','MemberController@getMemberGroupMembers');
    Route::post('addGroupMember','MemberController@addGroupMember');
    Route::post('deleteGroupMember','MemberController@deleteGroupMember');
});

Route::get('inviterCheck','MemberController@inviterCheck');
Route::post('extendMemberShip','MemberController@extendMemberShip');
Route::post('member/join','MemberController@store');





Route::apiresource('category','CategoryController');
Route::get('which_category_event/{name}','EventController@which_category_event');

Route::apiresource('event','EventController');
Route::post('joinevent/{slug}','EventController@JoinEvent');
Route::post('cancelevent/{slug}','EventController@CancelEVent');
Route::post('myevent','EventController@MyEvent');
Route::get('eventguests/{slug}','EventController@EventGuests');


Route::post('transaction','TransactionController@transaction');
Route::post('uploadImage','AuthController@uploadImage');
Route::get('trans-history/{id}','TransactionController@show');

Route::post('couponcode/exchange','PromocodeController@exchange');


