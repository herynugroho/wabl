<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
    
// });

// Route::get('/peg','App\Http\Controllers\SkpbmController.php@get_pegawai');
Route::post('/auth','SipController@login');

Route::post('/webhook', 'SipController@webhook');
Route::post('/getwa', 'SipController@get_wa');
Route::post('/updatewa', 'SipController@updatewa');
Route::post('/waselesai', 'SipController@waselesai');
Route::post('/getfaq', 'SipController@get_faq');
Route::post('/getguru', 'SipController@get_guru');
Route::post('/getchat', 'SipController@getchat');
Route::post('listwa', 'SipController@list_wa');