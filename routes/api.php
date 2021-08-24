<?php


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
    Route::post('set_pushtoken','AuthController@set_pushtoken');
    Route::post('login', 'AuthController@login');
    Route::post('line_login', 'AuthController@line_login');
    Route::post('signup', 'AuthController@signup');
    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::post('me', 'AuthController@me');
    Route::post('myAccount','MemberController@myAccount');
    Route::post('updateAccount','MemberController@updateAccount');
    Route::post('uploadImage','AuthController@uploadImage');
    Route::post('bind_lineAccount','AuthController@bind_lineAccount');
});


//管理員------------------------
Route::group(['middleware' => ['JWT','admin']], function () {

    //MemberController
    Route::get('get-members','MemberController@getMembers');
    Route::get('search-member','MemberController@searchMember');
    Route::post('changePayStatus','MemberController@changePayStatus');
    Route::get('getPayHistory/{id}','MemberController@getPayHistory');
    Route::get('getMemberDetail/{id}','MemberController@getMemberDetail');
    Route::post('toValid','MemberController@toValid')->middleware('role:accountant');
    Route::post('addGroupMember','MemberController@addGroupMember');
    Route::post('updateInviter','MemberController@updateInviter');
    
    Route::get('getUserLevel/{user_id}','MemberController@getUserLevel');
    Route::post('updateMemberAccount','MemberController@updateMemberAccount');
    Route::post('updateMemberPassword/{id_code}','MemberController@updateMemberPassword');
    Route::post('makeGroupLeader','MemberController@makeGroupLeader');
    Route::post('removeGroupLeader','MemberController@removeGroupLeader');
    Route::post('makeTeacher','MemberController@makeTeacher');

    Route::post('sendMoneyTo','TransactionController@sendMoneyTo');
    Route::post('sendMoneyToUsers','TransactionController@sendMoneyToUsers');
    Route::post('reserseTransaction','TransactionController@reserseTransaction');
});

//Guest 使用者------------------------

    //MemberController
    Route::get('getAllAssociation','MemberController@getAllAssociation');
    Route::get('inviterCheck','MemberController@inviterCheck');
    Route::post('extendMemberShip','MemberController@extendMemberShip');
    Route::post('member/join','MemberController@store');
    Route::post('updateMemberLevel','MemberController@updateMemberLevel');//should be auth , admin later

    //活動類別
    Route::apiresource('category','CategoryController');

    //活動
    //App Users
    Route::get('getEvents','EventController@getEvents');//舊的 版本更新後移除
    Route::get('event/eventList','EventController@eventList'); //v2
    Route::get('event/eventDetail/{slug}','EventController@eventDetail'); //v2

    Route::post('myevent','EventController@MyEvent');//should be auth later //舊的 版本更新後移除
    Route::get('event/myEventList','EventController@myEventList'); //v2

    Route::post('joinevent/{slug}','EventController@JoinEvent');//should be auth later //舊的 版本更新後移除
    Route::post('cancelevent/{slug}','EventController@CancelEVent');//should be auth later //舊的 版本更新後移除
    
    Route::get('district','EventController@GetDistrict');
    Route::get('which_category_event/{name}','EventController@which_category_event');
    Route::post('drawEventReward/{slug}','EventController@drawEventReward');//should be auth later //舊的 版本更新後移除
    Route::post('drawEventRewardV2/{slug}','EventController@drawEventRewardV2');
    Route::get('isUserArrive/{slug}','EventController@isUserArrive');
    Route::post('arriveEvent/{slug}','EventController@arriveEvent');
    //後台
    Route::apiresource('event','EventController');
    Route::get('eventguests/{slug}','EventController@EventGuests');
    Route::get('getRewardLevel','EventController@getRewardLevel');
    Route::post('updateEventCurrentDay/{slug}','EventController@updateEventCurrentDay');
    Route::post('updateEventPublicStatus','EventController@updateEventPublicStatus');

    Route::group(['prefix'=>'event'],function(){
        Route::get('/{slug}/certificate','EventController@show_certificate');
        Route::post('/{slug}/certificate','EventController@store_certificate');
        Route::post('/{slug}/certificate/update','EventController@update_certificate');
        Route::post('/{slug}/certificate/issue','EventController@issue_certificate');
        Route::get('/getEventManagers/{slug}','EventController@getEventManagers');
        Route::post('/addManager/{slug}','EventController@addManager');
        Route::post('/removeManager/{slug}','EventController@removeManager');
    });
    
    //交易
    Route::post('transaction','TransactionController@transaction');
    Route::get('trans-history/{id}','TransactionController@show');
    Route::get('transaction/myTransactionHistory','TransactionController@myTransactionHistory');
    Route::get('transaction/history/{id}','TransactionController@history');
    Route::get('transaction/list','TransactionController@list');

    //折扣兌換卷
    Route::post('couponcode/exchange','PromocodeController@exchange');

    //產品
    //App Users
    Route::apiresource('product','ProductController');
    Route::get('getLocationAndQuantity/{slug}','ProductController@getLocationAndQuantity');
    Route::get('product-category','ProductController@productCategory');
    Route::post('purchase/{Product}','ProductController@purchase');
    Route::post('purchaseByCash/{Product}','ProductController@purchaseByCash');
    
    Route::get('getAllProduct','ProductController@getAllProduct');  //舊的 版本更新後移除
    Route::get('productList','ProductController@productList');
    Route::get('/marketProductList','ProductController@marketProductList');

    //後台
    Route::get('productDetail/{slug}','ProductController@productDetail');
    Route::group([
        'prefix'=>'bcp/product',
        'middleware'=>['JWT','FirmAndAdmin'],
    ],function(){
        Route::post('updateOrderWeight','ProductController@updateOrderWeight');
    });
    //AppUser
    Route::get('product/productDetail/{slug}','ProductController@showV2');

    //經銷據點
    Route::apiresource('location','LocationController');
    Route::get('/locationList','LocationController@locationList');
    Route::post('updateLocation','LocationController@updateLocation');
    Route::post('insertLocation','LocationController@insertLocation');
    Route::get('/getLocationManagers/{slug}','LocationController@getLocationManagers');
    Route::post('/addManager/{slug}','LocationController@addManager');
    Route::post('/removeManager/{slug}','LocationController@removeManager');
    Route::get('/location/{slug}/productList','LocationController@api_productList');
    Route::post('/updateInventory','LocationController@updateInventory');
    Route::get('/inventory/{location_id}/{product_id}','LocationController@getInventory');
    
    //App User
    Route::group(['prefix'=>'cart'],function(){
        Route::post('store/{product_id}','CartController@store');
        Route::post('destroy/{product_id}','CartController@destroy');
    });

    //App Users
    //訂單
    Route::get('/my-order-list','OrderDetailController@myOrderList');//舊的 版本更新後移除
    Route::get('order/myOrderList','OrderDetailController@myOrderListV2');
    //後台
    Route::get('order/productOrderList/{product_id}','OrderDetailController@productOrderList');
    Route::post('order/deleteOrderDetail','OrderDetailController@deleteOrderDetail');

    //後台
    Route::group(['prefix'=>'order'],function(){
        Route::get('getOrders','OrderController@getOrders');
        Route::get('getOrderDetail/{order_numero}','OrderController@getOrderDetail');
        Route::post('nextStatus','OrderController@nextStatus');
        Route::post('groupNextStatus','OrderController@groupNextStatus');
        Route::post('{order_numero}/void','OrderController@voidOrder');
    });
    
    //後台 儀表板
    Route::group(['prefix'=>'dashboard'],function(){
        Route::get('getTotal','DashboardController@getTotal');
        Route::get('getValid','DashboardController@getValid');
        Route::get('getUnValid','DashboardController@getUnValid');
        Route::get('getAgeDist','DashboardController@getAgeDist');
        Route::get('getTotalWallet','DashboardController@getTotalWallet');
        Route::get('getBirthdayList','DashboardController@getBirthdayList');
        Route::get('getOrgRankSum5','DashboardController@getOrgRankSum5');
        Route::get('getOrgRankSum4','DashboardController@getOrgRankSum4');
        Route::get('getOrgRankSum3','DashboardController@getOrgRankSum3');
        Route::get('getOrgRankSum2','DashboardController@getOrgRankSum2');
        Route::get('getOrgRankSum1','DashboardController@getOrgRankSum1');
        Route::get('getDistrictSum','DashboardController@getDistrictSum');
        Route::get('getGroupleaders','DashboardController@getGroupleaders');
        Route::get('getGroupStatus','DashboardController@getGroupStatus');
    });


    Route::group(['prefix'=>'image'],function(){
        Route::post('upload/{type}/{slug}','ImageController@upload');
    });

    //App Users po文
    Route::group(['prefix'=>'post'],function(){
        Route::get('list','PostController@list');
        Route::get('myPostList','PostController@myPostList');
        Route::post('makeNewPost','PostController@makeNewPost');
        Route::post('likePost/{slug}','PostController@likePost');
        Route::post('unLikePost/{slug}','PostController@unLikePost');
        Route::get('detail/{slug}','PostController@detail');
        Route::post('commentOnPost/{slug}','PostController@commentOnPost');
        Route::post('removeComment','PostController@removeComment');
        Route::get('commentList/{slug}','PostController@commentList');
    });

    Route::group(['prefix'=>'insurance'],function(){
        
        Route::group(['middleware'=>['JWT','admin']],function(){
            Route::get('/','InsuranceController@index');
            Route::get('/{id}','InsuranceController@show');
            Route::put('/{id}/update','InsuranceController@update');
            Route::post('nextStatus','InsuranceController@nextStatus');
            Route::post('issue','InsuranceController@issue');
            Route::post('/{id}/void','InsuranceController@void');
        });

    });

    Route::apiresource('clinic','ClinicController')->middleware(['JWT','admin']);
    Route::group(['prefix'=>'clinic'],function(){
        Route::group(['middleware'=>['JWT','admin']],function(){
            
        });
    });