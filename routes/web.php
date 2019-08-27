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

Route::get('/member/join','MemberController@create');
Route::post('/member/join','MemberController@store');
Route::get('/member/welcome','MemberController@welcome');
// Route::get('/member/cacu','MemberController@cacu');

Route::view('/{any}','admin');
Route::view('/{any}/{any1}','admin');

