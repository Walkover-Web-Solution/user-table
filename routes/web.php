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

Route::get('/tables', 'UserController@getUserAllTables')->name('tables');
Route::get('socketlogin', 'LoginController@login');
Route::get('unauthorised', function () {
    return view('unauthorised');
})->name('unauthorised');


# To Show table data
Route::get('/tables/{tableName}', 'TableController@loadSelectedTable');

# Route for saved filters
Route::get('/tables/{tableName}/filter/{filterName}', 'TableController@loadSelectedTableFilterData');

# For create table view
Route::get('/createTable', function(){
    return view('createTable');
});

# for creating user table in database
Route::post('/createTable', 'UserController@createTable');

# for Configure user table in database
Route::get('/configure/{tableName}', 'TableController@loadSelectedTableStructure');

# for alter user table in database
Route::post('/configureTable', 'TableController@configureSelectedTable');
        
Route::get('/home/{tab}', 'HomeController@filterTab');
Route::get('/user_data/{id}', 'UserController@getDetailsOfUserById');
#serach by filters
//Route::post('/filter', 'UserController@applyFilters');
Route::post('/filter', 'TableController@applyFilters');

#save filter
Route::post('/filter/save', 'UserController@saveFilter');

# search in active table
Route::get('/search/{tab}/{query}', 'UserController@getSearchedData');

# to create or add user
Route::post('/add_update', 'TableController@add');
Route::get('/add_update', 'TableController@add');

Route::get('/profile','UserController@getKey')->name('profile');

Route::get('/getTables', 'UserController@getAllTablesForSocket')->middleware(['socketMasterKey']);

Route::post('/update', 'TableController@updateEntry');
