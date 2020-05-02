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
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

////Route::get('/invoice', 'HomeController@invoice');

Route::get('/invoice', function(){
    return view('invoice');
});

// Route::get('login/github', 'Auth\LoginController@redirectToProvider');
// Route::get('login/github/callback', 'Auth\LoginController@handleProviderCallback');
Route::get('login/{service}', 'Auth\LoginController@redirectToProvider');
Route::get('login/{service}/callback', 'Auth\LoginController@handleProviderCallback');

////비디오 설명에는 d-로 했는데 에러가 발생해 d로 수정.09/06/2019
////Route::get('{path}',"HomeController@index")->where( 'path','([A-z\d-\/_.]+)?' );
////아래코드는 항상 맨 아래에 위치해야한다. 그렇지 않은면 다른 Route가 not working...
Route::get('{path}',"HomeController@index")->where( 'path','([A-z\d\/_.]+)?' );
