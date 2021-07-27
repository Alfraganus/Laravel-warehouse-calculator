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
Route::get('/start/{product_id}','WarehouseController@getMaterials');
Route::get('/test2','WarehouseController@multipleProducts');
Route::get('/test3','WarehouseController@getCalculations');


Route::get('/warehouse/calculate', 'WarehouseController@test');
Route::get('/applications/edit/{id}', 'ApplicationController@show');
//Route::get('/test/editcha/{id}', 'WarehouseController@index');
Route::group(['middleware' => ['api']], function () {
Route::post('applications/get-ref', 'ApplicationController@getRef');
Route::post('applications/update', 'ApplicationController@update');
Route::delete('applications/delete/{id}', 'ApplicationController@destroy');
Route::get('suppliers', 'SupplierController@index');
});



