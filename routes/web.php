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
Route::group(['middleware' => ['web']], function() {
    Route::get('/', function () {
        return view('welcome');
    });
    Route::get('/login', function () {
        return view('welcome');
    })->name('login');

    Route::get('socketlogin', 'LoginController@login');
    Route::get('unauthorised', function () {
        return view('unauthorised');
    })->name('unauthorised');

    Route::post('logout', [
        'uses' => 'Auth\LoginController@logout'
    ])->name('logout');
});

Route::group(['middleware' => ['web', 'auth']], function() {
    Route::get('/getOptionList', 'ConfigureTable@getOptionList');
    Route::get('/tables', 'TableController@getUserAllTables')->name('tables');
# To Show table data
    Route::get('/tables/{tableName}', 'TableController@loadSelectedTable');
    Route::get('/graph/{tableName}', 'TableController@showGraphForTable');
    Route::get('/graphdata', 'TableController@getGraphDataForTable');
# Route for saved filters
    Route::get('/tables/{tableName}/filter/{filterName}', 'TableController@loadSelectedTableFilterData');
# For create table view
    Route::get('/createTable', function() {
        return view('createTable');
    })->name('createTable');
# for creating user table in database
    Route::post('/createTable', 'TableController@createTable');
# for Configure user table in database
    Route::get('/tables/structure/{tableName}', 'TableController@getSelectedTableStructure');
# for alter user table in database
    Route::post('/configureTable', 'TableController@configureSelectedTable');
    Route::get('/configure/{tableName}', 'TableController@loadSelectedTableStructure');
    Route::get('/table/{tableid}/user_data/{id}', 'UserController@getDetailsOfUserById');
    #serach by filters
    Route::post('/filter', 'TableController@applyFilters');

    #save filter
    Route::post('/filter/save', 'UserController@saveFilter');
    
    # Send SMS Email
    Route::post('/sendEmailSMS', 'TableController@sendEmailSMS');

    # search in active table
    Route::get('/search/{tableId}/{query}', 'TableController@getSearchedData');
    Route::get('/profile', 'UserController@getKey')->name('profile');

    Route::get('/getTables', 'TableController@getAllTablesForSocket')->middleware(['socketMasterKey']);

    Route::post('/update', 'TableController@updateEntry');
});

# to create or add user
Route::post('/add_update', 'TableController@add');
Route::get('/add_update', 'TableController@add');

//Auth::routes();
// Authentication Routes...
//Route::get('login', [
//  'as' => 'login',
//  'uses' => 'Auth\LoginController@showLoginForm'
//]);
//Route::post('login', [
//  'as' => '',
//  'uses' => 'Auth\LoginController@login'
//]);
//
//// Password Reset Routes...
//Route::post('password/email', [
//  'as' => 'password.email',
//  'uses' => 'Auth\ForgotPasswordController@sendResetLinkEmail'
//]);
//Route::get('password/reset', [
//  'as' => 'password.request',
//  'uses' => 'Auth\ForgotPasswordController@showLinkRequestForm'
//]);
//Route::post('password/reset', [
//  'as' => '',
//  'uses' => 'Auth\ResetPasswordController@reset'
//]);
//Route::get('password/reset/{token}', [
//  'as' => 'password.reset',
//  'uses' => 'Auth\ResetPasswordController@showResetForm'
//]);
//
//// Registration Routes...
//Route::get('register', [
//  'as' => 'register',
//  'uses' => 'Auth\RegisterController@showRegistrationForm'
//]);
//Route::post('register', [
//  'as' => '',
//  'uses' => 'Auth\RegisterController@register'
//]);
//Route::get('/home', 'HomeController@index')->name('home');
//Route::get('/home/{tab}', 'HomeController@filterTab');
