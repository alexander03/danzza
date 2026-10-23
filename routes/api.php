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


    Route::post('/AppGetCategorias', 'ApiServicesController@AppGetCategorias');
	Route::post('/AppGetProductoxCategoria', 'ApiServicesController@AppGetProductoxCategoria');

Route::group([

    'middleware' => 'api',

], function ($router) {

    Route::post('login', 'ApiServicesController@login');
    Route::post('logout', 'ApiServicesController@logout');
    Route::post('refresh', 'ApiServicesController@refresh');
    Route::post('me', 'ApiServicesController@me');
    Route::post('registrarPedido', 'ApiServicesController@registrarPedido');
    Route::post('editarDatos', 'ApiServicesController@editarDatos');
    Route::post('misPedidos', 'ApiServicesController@misPedidos');

});