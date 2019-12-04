<?php

namespace MelisPlatformFrameworkLaravel\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class ModuleCreatorServiceProvider extends ServiceProvider
{
    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->toolCreatorRoute();
    }

    /**
     * Define the "web" routes for the application.
     *
     * This routes will execute the tool creation in the side of
     * laravel Framework
     *
     * @return void
     */
    protected function toolCreatorRoute()
    {
        Route::middleware('web')->group(__DIR__.'/../ToolCreator/module-create.php');
    }
}
