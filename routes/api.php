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
    // Route::post('signup', 'AuthController@signup');
    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::post('me', 'AuthController@me');

});


Route::post('transaction','TransactionController@transaction');
Route::post('uploadImage','AuthController@uploadImage');
Route::get('trans-history/{id}','TransactionController@show');
Route::get('get-members','MemberController@getMembers');
Route::post('changePayStatus','MemberController@changePayStatus');
Route::post('executeExpired','MemberController@executeExpired');
Route::get('getPayHistory/{id}','MemberController@getPayHistory');
Route::get('getMemberDetail/{id}','MemberController@getMemberDetail');
Route::post('toValid','MemberController@toValid');
Route::get('inviterCheck','MemberController@inviterCheck');