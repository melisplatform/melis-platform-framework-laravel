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

Route::prefix('melis/moduletpl')->group(function() {

    Route::get('/tool', 'IndexController@index');

    #TCTOOLTYPE

    Route::post('/get-list', 'IndexController@list');

    Route::post('/save/{id?}', 'IndexController@save');

    Route::post('/delete/{id?}', 'IndexController@delete');
});
