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
Route::post('/test','WarehouseController@getMaterials');


Route::get('/warehouse/calculate', 'WarehouseController@index');
Route::get('/applications/edit/{id}', 'ApplicationController@show');
//Route::get('/test/editcha/{id}', 'WarehouseController@index');
Route::group(['middleware' => ['api']], function () {
Route::post('applications/get-ref', 'ApplicationController@getRef');
Route::post('applications/update', 'ApplicationController@update');
Route::delete('applications/delete/{id}', 'ApplicationController@destroy');
Route::get('suppliers', 'SupplierController@index');
});



