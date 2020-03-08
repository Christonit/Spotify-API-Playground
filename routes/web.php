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
    if(Auth::check()){
        $user =  Auth::user();
        return view('welcome')->with('user' ,$user);

    }

    return view('welcome');
});

// Auth::routes();

Route::get('/auth/spotify/callback', 'HomeController@callback');
Route::get('/auth/spotify', 'HomeController@authSpotify')->name('spotify');
Route::get('/auth/spotify/logout', 'HomeController@authLogout')->name('spotify-logout');
Auth::routes(['register' => false, 'login'=> false]);