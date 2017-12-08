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

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::group(['middleware' => ['verifysockettoken']], function() {
    Route::get('/tables', 'TableController@getAllTables');
});
Route::group(['middleware' => ['verifytabletoken']], function() {
    # to create or add user
    Route::post('/add_update', 'TableController@add');
});
