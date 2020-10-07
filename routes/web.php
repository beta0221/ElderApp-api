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


Route::post('web_login','AuthController@web_login');
Route::post('web_admin_login','AuthController@web_admin_login');
Route::get('web_login_page','AuthController@view_login')->name('web_login_page');
Route::get('web_admin_login_page','AuthController@view_admin_login')->name('web_admin_login_page');
Route::get('view_me','AuthController@view_me');

//Route::get('/sendMoney','TransactionController@sendMoney');

Route::get('event_reward_qrcode/{slug}','EventController@rewardQrCode');

Route::get('/member/join','MemberController@create');
Route::post('/member/join','MemberController@store');
Route::get('/member/welcome','MemberController@welcome');
Route::get('/member_tree/{id_code}','MemberController@memberTree');
Route::get('/memberGroupMembers','MemberController@memberGroupMembers');
Route::post('/removeMemberFromGroup','MemberController@removeMemberFromGroup');
Route::get('/moveMemberPage/{user_id}','MemberController@moveMemberPage');
Route::post('/moveMember/{user_id}','MemberController@moveMember');
Route::post('/promoteLeader','MemberController@promoteLeader');
Route::get('/coupon/generate/{quantity}/{amount}','PromocodeController@coupon_generate');

//商品兌換據點網頁
Route::get('/order-list/location/{slug}','LocationController@orderListPage');

//訂單
    //目前還沒有登入驗證所以傳四個參數提高一點安全性
Route::post('/order-detail/receive','OrderDetailController@receiveOrder');


Route::group(['prefix'=>'product'],function(){
    Route::get('list','ProductController@list');
    Route::get('detail/{slug}','ProductController@detail');
});

Route::group(['prefix'=>'cart'],function(){
    Route::get('/','CartController@index')->name('cart_page');
    Route::post('checkOut','CartController@checkOut');
});

Route::group(['prefix'=>'order'],function(){
    Route::get('downloadOrderExcel','OrderController@excel_downloadOrderExcel');
    Route::get('thankyou/{order_numero}','OrderController@view_thankyou');
    Route::get('detail/{order_numero}','OrderController@view_orderDetail');
    Route::get('list','OrderController@view_orderList');
});


Route::get('/app/product/{slug}','ProductController@universal_link');
Route::get('/app/event/{slug}','EventController@universal_link');
Route::get('/app/post/{slug}','PostController@universal_link');


Route::view('/supplier/{any}','supplierAdmin');
Route::view('/supplier/{any1}/{any}','supplierAdmin');

Route::view('/{any}','admin');
Route::view('/{any}/{any1}','admin');

