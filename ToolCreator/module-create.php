<?php

use MelisPlatformFrameworkLaravel\ToolCreator\ModuleCreator;
/**
 * Route for Module creation
 */
Route::get('/melis/laravel-module-create', function(){
    $moduleCreator = new ModuleCreator();
    $moduleCreator->run();

    return response()->json(['success' => 1]);
});