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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

# to create or add user
Route::middleware('auth:api')->post('/add_update', 'UserController@add');

#serach by filters
Route::middleware('auth:api')->post('/filter/{tab}', 'UserController@applyFilters');

Route::middleware('auth:api')->get('/search/{tab}/{query}', 'UserController@getSearchedData');
