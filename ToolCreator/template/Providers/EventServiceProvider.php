<?php

namespace Modules\ModuleTpl\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\ModuleTpl\Events\DeleteItemEvent;
use Modules\ModuleTpl\Events\SaveFormEvent;
use Modules\ModuleTpl\Listeners\DeleteItemRequest;
use Modules\ModuleTpl\Listeners\SaveFormRequest;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        #TCEVENTLISTENERS
    ];
}