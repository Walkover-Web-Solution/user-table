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
    Route::get('/search', 'TableController@searchTableData');
    Route::post('/search','TableController@filterTableData');
    Route::get('/contacts', 'TableController@getContacts');
    Route::get('/filters', 'TableController@getFilters');
});
