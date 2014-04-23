<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get('/', 'CronratController@getIndex');
Route::get('/in', 'CronratController@getIn');
Route::get('/help', 'CronratController@getFaq');

Route::get('/r/{ratkey?}', 'CronratUrl@getCr');

Route::post('/r/{ratkey?}', 'CronratUrl@getCr');

Route::controller('users', 'UserController');

Route::controller('cronrat', 'CronratController');

Route::controller('verify', 'VerifyController');

Route::resource('groups', 'GroupController');