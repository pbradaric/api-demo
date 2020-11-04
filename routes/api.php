<?php

use App\Picture;
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

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::group(['middleware' => ['auth:api']], function () {
    Route::get('pictures', 'PictureController@index');
    Route::get('pictures/{id}', 'PictureController@show');
    Route::post('pictures', 'PictureController@store');
    Route::put('pictures/{id}', 'PictureController@update');
    Route::delete('pictures/{id}', 'PictureController@delete');
});
