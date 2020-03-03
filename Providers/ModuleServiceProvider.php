<?php

namespace MelisPlatformFrameworkLaravel\Providers;

use Illuminate\Support\ServiceProvider;
use Collective\Html\HtmlFacade;
use MelisPlatformFrameworkLaravel\Helpers\DataTableHelper;
use Collective\Html\FormFacade As Form;
use MelisPlatformFrameworkLaravel\Helpers\FieldRowHelper;
use MelisPlatformFrameworkLaravel\Helpers\ZendEvent;

class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(ZendServiceProvider::class);

        $loader = \Illuminate\Foundation\AliasLoader::getInstance();
        $loader->alias('Form', Form::class);
        $loader->alias('Html', HtmlFacade::class);
        $loader->alias('DataTable', DataTableHelper::class);
        $loader->alias('FieldRow', FieldRowHelper::class);
    }

    public function boot()
    {
        $this->registerViews();
        $this->registerViewHelper();
        $this->registerTranslations();
    }

    /**
     * Register views.
     */
    public function registerViews()
    {
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'melisLaravel');
    }

    /**
     * Register form view helper
     */
    public function registerViewHelper()
    {
        Form::component('bsText', 'melisLaravel::form.text', ['name', 'label' => null, 'tooltip' => null, 'attributes' => [], 'value' => null]);

        Form::macro('melisFieldRow', function($formConfig, $data = null, $defaultData = []){
            return FieldRowHelper::createFields($formConfig, $data, $defaultData);
        });
    }

    /**
     * Register translations.
     */
    public function registerTranslations()
    {
        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'melisLaravel');
    }

}
