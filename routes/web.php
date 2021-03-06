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

Route::get('/check',function(){
    return response('alive');
});


Route::post('web_login','AuthController@web_login');
Route::post('web_admin_login','AuthController@web_admin_login');
Route::get('web_login_page','AuthController@view_login')->name('web_login_page');
Route::get('web_admin_login_page','AuthController@view_admin_login')->name('web_admin_login_page');
Route::get('view_me','AuthController@view_me');

Route::get('event_reward_qrcode/{slug}','EventController@rewardQrCode');
//老師管理後台
Route::get('/view_myCourse','EventController@view_myCourse');

Route::get('/member/join','MemberController@create');
Route::post('/member/join','MemberController@store');
Route::get('/member/welcome','MemberController@welcome');
Route::get('/member_tree/{id_code}','MemberController@memberTree');

Route::get('/memberGroupMembers','MemberController@memberGroupMembers');
Route::get('/memberGroupMembers_list','MemberController@memberGroupMembers_list');
Route::get('/excel/memberGroupMembers','MemberController@excel_memberGroupMembers')->middleware('role:admin');

Route::get('getGroupMemberDetail/{id}','MemberController@getMemberDetail')->middleware('JWT');
Route::post('/removeMemberFromGroup','MemberController@removeMemberFromGroup');
Route::get('/moveMemberPage/{user_id}','MemberController@moveMemberPage');
Route::post('/moveMember/{user_id}','MemberController@moveMember');
Route::post('/promoteLeader','MemberController@promoteLeader');
Route::get('/coupon/generate/{quantity}/{amount}','PromocodeController@coupon_generate');

//商品兌換據點網頁
Route::get('/order-list/location/{location_slug}','LocationController@view_productList')->name('productList');
Route::get('/order-list/location/{location_slug}/{product_slug}','LocationController@view_orderList')->name('orderList');
Route::get('/receive-list/location/{location_slug}/{product_slug}','LocationController@view_receiveList')->name('receiveList');
Route::get('/view_myLocation','LocationController@view_myLocation');
Route::get('/view_locationOrderList/{slug}','LocationController@view_locationOrderList');
Route::get('/view_locationOrderDetail/{slug}/{order_numero}','LocationController@view_locationOrderDetail');
Route::post('/view_nextStatus/{slug}/{order_numero}','LocationController@view_nextStatus');

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
    Route::get('locationOrderExcel','OrderController@excel_locationOrderExcel');
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

Route::view('/association/{any}','associationAdmin');
Route::view('/association/{any}/{any1}','associationAdmin');

Route::view('/{any}','admin');
Route::view('/{any}/{any1}','admin');

