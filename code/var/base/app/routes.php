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

Route::get('/', 'HomeController@showWelcome');

//TODO Route::get('/', 'CronratController@getIndex');

Route::controller('users', 'UserController');

Route::controller('cronrat', 'CronratController');

Route::controller('verify', 'VerifyController');

Route::resource('groups', 'GroupController');

Route::controller('emd', 'EmdController');




