<?php

namespace Modules\ModuleTpl\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;

class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
        $this->app->register(EventServiceProvider::class);
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            module_path('ModuleTpl', 'Config/config.php') => config_path('moduletpl.php'),
        ], 'config');
        $this->mergeConfigFrom(
            module_path('ModuleTpl', 'Config/config.php'), 'moduletpl'
        );
        $this->mergeConfigFrom(
            module_path('ModuleTpl', 'Config/table.config.php'), 'moduletpl'
        );
        $this->mergeConfigFrom(
            module_path('ModuleTpl', 'Config/form.config.php'), 'moduletpl'
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/moduletpl');

        $sourcePath = module_path('ModuleTpl', 'Resources/views');

        $this->publishes([
            $sourcePath => $viewPath
        ],'views');

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path . '/modules/moduletpl';
        }, Config::get('view.paths')), [$sourcePath]), 'moduletpl');
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/moduletpl');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'moduletpl');
        } else {
            $this->loadTranslationsFrom(module_path('ModuleTpl', 'Resources/lang'), 'moduletpl');
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
