<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('admin');
});

Route::get('play',function(){
    return view('playground');
});

//Route::get('sendMoney','TransactionController@sendMoney');

Route::get('event_reward_qrcode/{slug}','EventController@rewardQrCode');

Route::get('/member/join','MemberController@create');
Route::post('/member/join','MemberController@store');
Route::get('/member/welcome','MemberController@welcome');
Route::get('/member_tree/{id_code}','MemberController@memberTree');
Route::post('/removeMemberFromGroup','MemberController@removeMemberFromGroup');
Route::get('/moveMemberPage/{user_id}','MemberController@moveMemberPage');
Route::post('/moveMember/{user_id}','MemberController@moveMember');
// Route::get('/member/cacu','MemberController@cacu');
Route::get('/coupon/generate/{quantity}/{amount}','PromocodeController@coupon_generate');

//商品兌換據點網頁
Route::get('/order-list/location/{slug}','LocationController@orderListPage');

//訂單
    //目前還沒有登入驗證所以傳四個參數提高一點安全性
Route::post('/order-detail/receive','OrderDetailController@receiveOrder');

Route::view('/{any}','admin');
Route::view('/{any}/{any1}','admin');

